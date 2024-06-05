<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use App\Http\Controllers\Test\UtilidadesTest;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use SoapClient;
use SimpleXMLElement;

class ShopifyGraphQLTest extends Controller
{
	public function createUser(Request $request) {
		/*
		 * CORS BYPASS - Only DEV
		 */

		/*
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		header("Allow: GET, POST, OPTIONS, PUT, DELETE");
		$method = $_SERVER['REQUEST_METHOD'];
		if($method == "OPTIONS") {
			die();
		}
		*/

		$customer = $request->all()['customer'];

		$accept = "false";
		if (isset($customer['accepts_marketing'])) {
			$accept = $customer['accepts_marketing'];
			if (isset($request->all()['interest'])) {
				$interest = $request->all()['interest'];
				$interest_array = ["int"];
				if (isset($interest["men"])) {
					array_push($interest_array, $interest["men"]);
				}
				if (isset($interest["women"])) {
					array_push($interest_array, $interest["women"]);
				}
				if (isset($interest["kids"])) {
					array_push($interest_array, $interest["kids"]);
				}
				$int_tag = implode("-", $interest_array);
			} else {
				$int_tag = '';
			}
		} else {
			$int_tag = '';
		}

		if ($request->all()['fullForm'] == "false") {
			$user = '{
				"customer": {
					"first_name":"' . $customer['first_name'] .'",
					"last_name":"' . $customer['last_name'] .'",
					"password":"' . $customer['password'] .'",
					"password_confirmation":"' . $customer['password'] .'",
					"accepts_marketing":"' . $accept . '",
					"email":"' . $customer['email'] .'",
					"tags": ["nofid"]
					}
				}';
		} else {
			$user = '{
				"customer": {
					"first_name":"' . $customer['first_name'] .'",
					"last_name":"' . $customer['last_name'] .'",
					"password":"' . $customer['password'] .'",
					"password_confirmation":"' . $customer['password'] .'",
					"accepts_marketing":"' . $accept .'",
					"email":"' . $customer['email'] .'",
					"tags" : ["tier: Bronze","' . $int_tag . '"],
					"metafields": [
						{
						  "key": "birthday_date",
						  "value": "' . $customer['birth'] .'",
						  "value_type": "string",
						  "namespace": "customr"
						},
						{
						  "key": "gender",
						  "value": "' . $customer['gender'] .'",
						  "value_type": "string",
						  "namespace": "customr"
						},
						{
						  "key": "phone_number",
						  "value": "' . $customer['phone'] .'",
						  "value_type": "string",
						  "namespace": "customr"
						},
						{
						  "key": "postal_code",
						  "value": "' . $customer['zip'] .'",
						  "value_type": "string",
						  "namespace": "customr"
						}
					]
				}
			}';
		}

		$sfy_customer_check = Utilidades::shopifyRESTAPI('GET', 'customers/search.json?query=email:'.$customer['email'], Utilidades::shopifyStoreSel('ES'), "");

		if (isset($sfy_customer_check["customers"][0]["state"])) {
			if ($sfy_customer_check["customers"][0]["state"] == "disabled") {
				if ($request->all()['fullForm'] != "false") {
					$sfy_customer_check["customers"][0]["birth"] = $customer['birth'];
					$sfy_customer_check["customers"][0]["gender"] = $customer['gender'];
					$sfy_customer_check["customers"][0]["phone"] = $customer['phone'];
					$sfy_customer_check["customers"][0]["zip"] = $customer['zip'];
				}
				$request_upt = new \Illuminate\Http\Request();
				$request_upt->merge(["customer" => $sfy_customer_check["customers"][0]]);
				return self::updateUser($request_upt);
			}
		}

		$sfy_customer_es = Utilidades::shopifyRESTAPI('POST', 'customers.json', Utilidades::shopifyStoreSel('ES'), $user);
		//$sfy_customer_test = Utilidades::shopifyRESTAPI('POST', 'customers.json', Utilidades::shopifyStoreSel('T1'), $user);

		if (isset($sfy_customer_es["customer"])) {
			$n =0;
			do {
				$lty_customer = Utilidades::getLoyaltyCustomer($customer["email"], env('LTY_API_KEY'), env('LTY_API_PWD'));
				$n++;
			} while(!isset($lty_customer["customers"][0]));
			Utilidades::updateLoyaltyUserBirthday($customer["email"], $lty_customer["customers"][0]["merchant_id"], $customer["birth"], env('LTY_API_KEY'), env('LTY_API_PWD'));
		}
		return $sfy_customer_es;
	}

	public function recursive_change_key($arr, $set) {
    	if (is_array($arr) && is_array($set)) {
     		$newArr = array();
     		foreach ($arr as $k => $v) {
    		    $key = array_key_exists( $k, $set) ? $set[$k] : $k;
    		    $newArr[$key] = is_array($v) ? recursive_change_key($v, $set) : $v;
     		}
			return $newArr;
		}
     	return $arr;
    }

	public function updateUser(Request $request) {
		/*
		 * CORS BYPASS - Only DEV
		 */

		/*
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		header("Allow: GET, POST, OPTIONS, PUT, DELETE");
		$method = $_SERVER['REQUEST_METHOD'];
		if($method == "OPTIONS") {
			die();
		}
		*/

		$customer = $request->all()['customer'];

		$sfy_customer_full = Utilidades::shopifyRESTAPI('GET', 'customers/' . $customer['id'] . '.json', Utilidades::shopifyStoreSel('ES'), "namespace=customr");
		$sfy_customer_metafields = Utilidades::shopifyRESTAPI('GET', 'customers/' . $customer['id'] . '/metafields.json', Utilidades::shopifyStoreSel('ES'), "namespace=customr")["metafields"];

		$sfy_customer_tags =  explode(", ", $sfy_customer_full["customer"]["tags"]);

		if (isset($sfy_customer_tags)) {
			$nofid_check = in_array("nofid", $sfy_customer_tags);
			if ($nofid_check){
				$key = array_search('nofid', $sfy_customer_tags);
				unset($sfy_customer_tags[$key]);
				$key2 = array_search('tier: No Fidelizado', $sfy_customer_tags);
				unset($sfy_customer_tags[$key2]);
				array_push($sfy_customer_tags, "tier: Bronze");
			}
		}

		$user = [
			"customer" => [
				"id" => $customer['id'],
				"email" => $sfy_customer_full['customer']['email'],
				"tags" => array_values($sfy_customer_tags),
				"metafields" => []
			]
		];

		$birth_update = '';
		$gender_update = '';
		$phone_update = '';
		$zip_update = '';

		foreach ($sfy_customer_metafields as $key => $value) {
			switch($sfy_customer_metafields[$key]["key"]) {
				case 'birthday_date':
					if ($customer['birth'] != $sfy_customer_metafields[$key]['value']) {
						$birth_update = Utilidades::shopifyRESTAPI('PUT', 'metafields/' . $sfy_customer_metafields[$key]["id"] . '.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": "' . $customer['birth'] . '" } }');
					} else {
						$birth_update = true;
					}
					$birth_already_exist = true;
				break;
				case 'gender':
					if ($customer['gender'] != $sfy_customer_metafields[$key]['value']) {
						$gender_update = Utilidades::shopifyRESTAPI('PUT', 'metafields/' . $sfy_customer_metafields[$key]["id"] . '.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": "' . $customer['gender'] . '" } }');
					} else {
						$gender_update = true;
					}
				break;
				case 'phone_number':
					if ($customer['phone'] != $sfy_customer_metafields[$key]['value']) {
						$phone_update = Utilidades::shopifyRESTAPI('PUT', 'metafields/' . $sfy_customer_metafields[$key]["id"] . '.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": "' . $customer['phone'] . '" } }');
					} else {
						$phone_update = true;
					}
				break;
				case 'postal_code':
					if ($customer['zip'] != $sfy_customer_metafields[$key]['value']) {
						$zip_update = Utilidades::shopifyRESTAPI('PUT', 'metafields/' . $sfy_customer_metafields[$key]["id"] . '.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": "' . $customer['zip'] . '" } }');
					} else {
						$zip_update = true;
					}
				break;
			}
		}

		if (!$birth_update) {
			array_push($user["customer"]["metafields"], ["key" => "birthday_date", "value" => $customer['birth'], "value_type" => "string", "namespace" => "customr"]);
		}
		if (!$gender_update) {
			array_push($user["customer"]["metafields"], ["key" => "gender", "value" => $customer['gender'], "value_type" => "string", "namespace" => "customr"]);
		}
		if (!$phone_update) {
			array_push($user["customer"]["metafields"], ["key" => "phone_number", "value" => $customer['phone'], "value_type" => "string", "namespace" => "customr"]);
		}
		if (!$zip_update) {
			array_push($user["customer"]["metafields"], ["key" => "postal_code", "value" => $customer['zip'], "value_type" => "string", "namespace" => "customr"]);
		}

		$sfy_customer = Utilidades::shopifyRESTAPI('PUT', 'customers/' . $customer['id'] . '.json', Utilidades::shopifyStoreSel('ES'), json_encode($user, true));

		if (!isset($birth_already_exist)) {
			do {
				$lty_customer = Utilidades::getLoyaltyCustomer($sfy_customer_full['customer']['email'], env('LTY_API_KEY'), env('LTY_API_PWD'));
			} while(!isset($lty_customer["customers"][0]));
			Utilidades::updateLoyaltyUserBirthday($sfy_customer_full['customer']['email'], $lty_customer["customers"][0]["merchant_id"], $customer["birth"], env('LTY_API_KEY'), env('LTY_API_PWD'));
		}

		return $sfy_customer;
	}

	public function index(Request $request)
    {

		//echo nl2br("Shopify GraphQL test - Get Store Name\n\n");

		//return $shop_name;

		//$shop_metafields = Utilidades::shopifyRESTAPI('PUT', 'metafields/11303956283492.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": 95 } }');

		//$shop_metafields = Utilidades::shopifyRESTAPI('GET', 'metafields.json', Utilidades::shopifyStoreSel('T1'), "namespace=bfriday");

		//return $shop_metafields;

		// $lty_customer = Utilidades::getLoyaltyCustomer(
		// 			'emelero@scalperscompany.com',
		// 			env('LTY_API_KEY'),
		// 			env('LTY_API_PWD')
		// 		); // Array

        // return $lty_customer["customers"];

		//$testdest = ['37.3918672,-5.9775277', '37.3918672,-5.9775277'];
		//$test1 = Utilidades::getDistanceMatrix('41510', $testdest, 'ES');

		//$location = $test1["results"][0]["geometry"]["location"];
		//$lat = $location["lat"];
		//$lng = $location["lng"];
		//$distance = Utilidades::getDistanceBetweenCoords(37.3890924,-5.9844589,$lat,$lng);
		//return $test1;

		// FEED TEST BEGIN

		return;

		$updateorder = '{
			"order": {
			  "id": 2066625134653,
			  "note_attributes": [
				{
				  "name": "NoStockOnline",
				  "value": "TEST"
				}
			  ]
			}
		}';

		return Utilidades::retrieveShopifyCustomerByEmail(Utilidades::shopifyStoreSel("ES"), "emelero@scalperscompany.com");

		return $updateorder;

		$update = Utilidades::shopifyRESTAPI('PUT', 'orders/2066625134653.json', Utilidades::shopifyStoreSel('ES'), $updateorder);
		return $update;

		$data = '{
			"smart_collection": {
			  "handle": "prueba-scalpify",
			  "title": "Prueba Scalpify",
			  "rules": [
				{
				  "column": "title",
				  "relation": "starts_with",
				  "condition": "scalpify"
				}
			  ]
			}
		  }';

		$stores = [
			"T1",
			"ES",
			"FR"
		];

		$calls = [];

		/* foreach ($stores as $key => $value) {
			// $calls[$key] = $value;
			$call = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel($value), $data, "2020-01");
			if (!isset($call["errors"])) {
				$calls[$key] = "ok";
			} else {
				$calls[$key] = "error";
			}
		} */

		$fila = 1;
		if (($gestor = fopen($request->file('filename'), "r")) !== FALSE) {
			while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
				$numero = count($datos);
				echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
				$fila++;
				for ($c=0; $c < $numero; $c++) {
					echo $datos[$c] . "<br />\n";
				}
			}
			fclose($gestor);
		}

		return;

		$smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('T1'), $data, "2020-01");
		//$smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('ES'), $data, "2020-01");
		// $smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('FR'), $data, "2020-01");
		// $smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('PT'), $data, "2020-01");
		// $smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('EU'), $data, "2020-01");
		// $smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('WW'), $data, "2020-01");
		// $smartcollection = Utilidades::shopifyRESTAPI('POST', 'smart_collections.json', Utilidades::shopifyStoreSel('T1'), $data, "2020-01");
		// $shop_metafields = Utilidades::shopifyRESTAPI('GET', 'metafields.json', Utilidades::shopifyStoreSel('T1'), "namespace=collections");
		return $smartcollection;

		// END TEST --------------------------------------------------------

		$cust_properties = [
			'$email' => 'emelero123@scalperscompany.com'
		];
		$properties = [
			'Ordered Product Value' => '55.90',
			'Name' => 'JERSEY CUELLO BOTONES',
			'ProductID' => '3957740929069',
			'Quantity' => '1',
			'SKU' => '8433740756968',
			'Variant Name' => 'GREY / S',
			'Variant Option: Color' => 'GREY',
			'Variant Option: Talla' => 'S',
			'Vendor' => 'Scalpers',
		];
		$kly_request = UtilidadesTest::klaviyoTrackAPITest(Utilidades::shopifyStoreSel('ES'), 'Pedido offline', json_encode($cust_properties), json_encode($properties));

		return $kly_request;

		$properties = [
			'$email' => 'emelero123@scalperscompany.com',
			'bf_reward' => '1'
		];
		$kly_request = UtilidadesTest::klaviyoIdentifyAPITest(Utilidades::shopifyStoreSel('ES'), json_encode($properties));

		return $kly_request;

		$time_start = microtime(true);

		$store = Utilidades::shopifyStoreSel('ES');

		$query = '{
			products(query:"inventory_total:>0 AND published_status:published") {
			  edges {
				node {
				  id
				  handle
				  title
				  description
				  productType
				  vendor
				  onlineStoreUrl
				  tags
				  createdAt
				  updatedAt
				  publishedAt
				  totalVariants
				  totalInventory
				  featuredImage {
				    id
					originalSrc
				  }
				  images {
				    edges {
					  node {
					    id
					    originalSrc
					  }
				    }
				  }
				  variants {
				    edges {
					  node {
					    id
					    sku
					    price
					    compareAtPrice
					  }
				    }
				  }
				  collections {
				    edges {
					  node {
					    id
						title
					  }
					}
				  }
				}
			  }
			}
		  }';

		$products = file_get_contents(Utilidades::shopifyBulkOperation($store, $query)["data"]["currentBulkOperation"]["url"]);
		//$products = Utilidades::shopifyBulkOperation($store, $query);

		$fileNameJsonl = time() . '_products_feed_export.jsonl';
		$fileNameCsv = time() . '_products_feed_export.csv';
		$fileNameXml = 'fb_es_feed.xml';

		// Almacenamos JSONL y liberamos memoria.
		Storage::put(('/public/jsonl/'.$fileNameJsonl), $products);
		$product = '';


		$jsonl = Storage::get('/public/jsonl/'.$fileNameJsonl);
		$handle = fopen("../storage/app/public/jsonl/".$fileNameJsonl, "r");

		$prod_array = [];

		do {
			$jsonl = fgets($handle);
			if ($jsonl != '') {

				$decode = json_decode($jsonl, true);
				$header = array_keys($decode);

				if (strpos($decode["id"], "/Product/") !== false) { //Main

					(isset($decode["tags"]) ? $decode["tags"] = implode(',', $decode["tags"]) : '');
					(isset($decode["featuredImage"]) ? $decode["featuredImage"] = $decode["featuredImage"]["originalSrc"] : '');

					//fputcsv($csvhandle, array_values($decode));
					array_push($prod_array, $decode);

				} else if (strpos($decode["id"], "/ProductImage/") !== false) { //Images
					foreach ($prod_array as $key => $value) {
						if ($prod_array[$key]["id"] == $decode["__parentId"]) {
							if (!isset($prod_array[$key]["images"])) {
								$prod_array[$key]["images"] = $decode["originalSrc"];
							} else {
								$prod_array[$key]["images"] = $prod_array[$key]["images"].','.$decode["originalSrc"];
							}
						}
					}
				} else if (strpos($decode["id"], "/ProductVariant/") !== false) { //Variants
					foreach ($prod_array as $key => $value) {
						if ($prod_array[$key]["id"] == $decode["__parentId"]) {
							if (!isset($prod_array[$key]["sku"])) {
								$prod_array[$key]["sku"] = $decode["sku"];
							}
							if (!isset($prod_array[$key]["price"])) {
								$prod_array[$key]["price"] = $decode["price"];
							}
							if (!isset($prod_array[$key]["compareAtPrice"])) {
								$prod_array[$key]["compareAtPrice"] = $decode["compareAtPrice"];
							}
						}
					}
				}
				else if (strpos($decode["id"], "/Collection/") !== false) { //Collections
					foreach ($prod_array as $key => $value) {
						if ($prod_array[$key]["id"] == $decode["__parentId"]) {
							if (!isset($prod_array[$key]["collections"])) {
								$prod_array[$key]["collections"] = $decode["title"];
							} else {
								$prod_array[$key]["collections"] = $prod_array[$key]["collections"].','.$decode["title"];
							}
						}
					}
				}
			}

		} while(!feof($handle));

		fclose($handle);

		// CSV

		/*
		Storage::put(('/public/csv/'.$fileNameCsv), '');
		$csvhandle = fopen("../storage/app/public/csv/".$fileNameCsv, "w");
		// Csv UTF-8 fix
		fputs($csvhandle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

		foreach ($prod_array as $key => $value) {
			if ($key === 0) {
				fputcsv($csvhandle, array_keys($prod_array[$key]));
			}
			fputcsv($csvhandle, array_values($prod_array[$key]));

		}

		fclose($csvhandle);
		*/

		// BUILDER

		Storage::disk('feeds')->put(($fileNameXml), '');
		$xmlhandle = fopen("../public/feeds/".$fileNameXml, "w");

		$feed_xmlns = "http://www.w3.org/2005/Atom";
		$feed_xmlns_g = "http://base.google.com/ns/1.0";

		fputs($xmlhandle, '<feed xmlns="' . $feed_xmlns . '" xmlns:g="' . $feed_xmlns_g . '">');

		foreach ($prod_array as $key => $value) {

			// TODO: Main Feed LOOP
			fputs($xmlhandle, '<entry>');

			(isset($prod_array[$key]["id"]) ? fputs($xmlhandle, '<g:id><![CDATA[' . str_replace("gid://shopify/Product/","shopify_ID_",$prod_array[$key]["id"]) . ']]></g:id>') : '');

			if (isset($prod_array[$key]["title"])) {
				fputs($xmlhandle, '<title><![CDATA[' . $prod_array[$key]["title"] . ']]></title>');
				fputs($xmlhandle, '<title_org><![CDATA[' . $prod_array[$key]["title"] . ']]></title_org>');
			}

			if (isset($prod_array[$key]["featuredImage"])) {
				fputs($xmlhandle, '<g:image_link><![CDATA[' . $prod_array[$key]["featuredImage"] . ']]></g:image_link>');
			}

			if (isset($prod_array[$key]["vendor"])) {
				fputs($xmlhandle, '<g:brand><![CDATA[' . $prod_array[$key]["vendor"] . ']]></g:brand>');
			}

			if (isset($prod_array[$key]["description"])) {
				fputs($xmlhandle, '<description><![CDATA[' . $prod_array[$key]["description"] . ']]></description>');
			}

			fputs($xmlhandle, '</entry>');
		}

		fputs($xmlhandle, '</feed>');
		$prod_array = [];
		fclose($xmlhandle);

		$time_end = microtime(true) - $time_start;
		$peak_mem = memory_get_peak_usage();

		$feed_url = 'TODO';
		$end = [ 'status' => 'COMPLETED', "feedurl" => $feed_url, 'processTime' => $time_end, 'peakMemoryUsage' => $peak_mem];
		return json_encode($end, true);

		// FEED TEST END

		$client = new SoapClient("https://ws.seur.com/webseur/services/WSConsultaExpediciones?wsdl", [ 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true, 'exception' => true, 'encoding' => 'ISO-8859-1', 'soap_version' => SOAP_1_2 ]);
		//$parametros = array('in0'=>'T1P289264');
		//$resultado = $client->__soapCall("consultaExpedicionesStr", array('parameters' => $parametros));
		$resultado = $client->__getFunctions();
		return $resultado;
		//print_r($resultado);

	}

	public function getLocationStock($id, $address) {

		// POSTMAN 600-1000ms
		// FRONT+APPPROXY 1600-2000ms

		$query = '{ productVariant(id: "gid://shopify/ProductVariant/' . $id . '") { inventoryItem { inventoryLevels(first: 50) { edges { node { location { name address { address1 city phone zip latitude longitude } } available } } } } } }';

		$request = Utilidades::shopifyGraphQL(Utilidades::shopifyStoreSel('T1'), $query);
		$locations = $request['data']['productVariant']['inventoryItem']['inventoryLevels']['edges'];

		$shopParseds = [];
		$destArray = [];
		$threshold = 150; // Kilómetros

		$henry = ($address != 'all');

		if (is_array($locations)) {
			for ($i = 0; $i <= count($locations)-1; $i++) {

				$location = $locations[$i];

				$name = $location['node']['location']['name'];
				$stock = $location['node']['available'];

				if ($name != 'Almacén Central') {

					if (isset($location['node']['location']['address'])) {
						$addressLine = $location['node']['location']['address'];
						$street = (isset($addressLine['address1'])) ? $addressLine['address1'] : '';
						$city = (isset($addressLine['city'])) ? $addressLine['city'] : '';
						$zip = (isset($addressLine['zip'])) ? $addressLine['zip'] : '';
						$lat = (isset($addressLine['latitude'])) ? $addressLine['latitude'] : '';
						$lng = (isset($addressLine['longitude'])) ? $addressLine['longitude'] : '';
						(isset($addressLine['phone'])) ? $addressLine['phone'] = str_replace('+34','',$addressLine['phone']) : $addressLine['phone'] = '';
					}

					if ($lat && $lng && $henry) {
						array_push($destArray, $lat.','.$lng);
					} else if ($street && $city && $zip && $henry) {
						array_push($destArray, $street.' '.$city.' '.$zip);
					} else if ($street && $city && $henry) {
						array_push($destArray, $street.' '.$city);
					} else if ($zip && $henry) {
						array_push($destArray, $zip);
					}

					($stock < 5) ? $stock : $stock = '5 o más';
					($stock !== 0) ? $stock : $stock = 'Sin stock';
					array_push($shopParseds, ['name'=> $name, 'stock' => $stock, 'address' => $addressLine]);

				}
			}

			if ($henry) {
				$matrix = Utilidades::googleDistanceMatrix(array($address), $destArray, 'ES');

				// Añadimos nueva key cuyo valor es la distancia entre origen y destino
				for ($i = 0; $i <= count($shopParseds)-1; $i++) {
					$shopParseds[$i]['distance'] = round(($matrix['rows'][0]['elements'][$i]['distance']['value'])/1000);
				}

				// Filtramos el array y eliminamos los índices cuya distancia sea mayor al límite
				$shopParseds = array_values(array_filter($shopParseds, function ($prop) use ($threshold){
					if ($prop['distance'] <= $threshold) {
						return true;
					}
					return false;
				}));

				// Por último, ordenamos de forma ascendente por proximidad
				usort($shopParseds, function ($item1, $item2) {
					return $item1['distance'] <=> $item2['distance'];
				});
			} else {
				// Si no recibimus address, ordenamos de forma descendente por stock
				usort($shopParseds, function ($item1, $item2) {
					return $item2['stock'] <=> $item1['stock'];
				});
			}

		} else {
			$shopParseds = '';
		}

		//dd($destArray); //DEBUG
		return $shopParseds;
	}

	public function testTrack($id='') {

		if (!isset($id)) {
		   exit("Tracking code missing");
	   }

	   // Creamos cliente SOAP con el WS de consulta de expediciones
	   $client = new SoapClient("https://ws.seur.com/webseur/services/WSConsultaExpediciones?wsdl", [ 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => true, 'exception' => true, 'encoding' => 'UTF-8', 'soap_version' => SOAP_1_1 ]);
	   $parametros = array('in0'=>'L', 'in1'=>'', 'in2'=>'', 'in3'=>$id, 'in4'=>'', 'in5'=>'', 'in6'=>'', 'in7'=>'', 'in8'=>'', 'in9'=>'', 'in10'=>'', 'in11'=>0, 'in12'=>env("SEUR_ES_USER"), 'in13'=>env("SEUR_ES_PWD"), 'in14'=>'S');
	   $response = $client->__soapCall("consultaListadoExpedicionesStr", array('parameters' => $parametros));
	   //$resultado = $client->__getFunctions();

	   // Limpiamos respuesta y creamos xml
	   $cleaner = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response->out);
	   $xml = new SimpleXMLElement($cleaner);

	   // Output. JSON.
	   return response()->json($xml, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
	}
}
