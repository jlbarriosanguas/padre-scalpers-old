<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ShopifyTempController extends Controller
{
	public function checkLocationStock($locid, $sku)
	{
		
		$query = '{
			inventoryItems(query:"sku:' . $sku . '", first:1) {
			  edges {
				node {
				  sku
				  inventoryLevel (locationId:"gid://shopify/Location/' . $locid . '") {
					 available
					 id
				  }
				}
			  }
			}
		}';
		
		$request = Utilidades::shopifyGraphQL(Utilidades::shopifyStoreSel('ES'), $query, '{ }');
		
		if (isset($request["data"]["inventoryItems"]["edges"][0])) {
			$available = $request["data"]["inventoryItems"]["edges"][0]["node"]["inventoryLevel"]["available"];
			return ($available > 0) ? 1 : 0;
		}

	}

	public function checkLocationStockById($locid, $varid)
	{
		
		$query = '{
			productVariant(id: "gid://shopify/ProductVariant/' . $varid . '") {
			  inventoryItem {
				inventoryLevel(locationId: "gid://shopify/Location/' . $locid . '") {
				  available
				  id
				}
			  }
			}
		}';
		
		$request = Utilidades::shopifyGraphQL(Utilidades::shopifyStoreSel('ES'), $query, '{ }');
		
		if (isset($request["data"]["productVariant"]["inventoryItem"]["inventoryLevel"]["available"])) {
			$available = $request["data"]["productVariant"]["inventoryItem"]["inventoryLevel"]["available"];
			return ($available > 0) ? 1 : 0;
		}
		
		return "Unknown InventoryItem";
	}

	public function captureSolidarityShirt(Request $request)
	{
		if (Utilidades::shopifyVerifyWebhook($request)) {
			DB::table('solidarity_counter')->where('property', 'trigger_counter')->increment('value');
			// DB::table('solidarity_counter')->where('property', 'trigger_counter')->update(['debug' =>$request]);

			$order = $request->json()->all();

			$eanarray = [
				"8445279010169", // XS
				"8445279010176", // S
				"8445279010183", // M
				"8445279010190", // L
				"8445279010206", // XL
				"8445279010213"  // XXL
			];

			if (isset($order["line_items"])) {
				foreach ($order["line_items"] as $key => $value) {
					if (in_array($order["line_items"][$key]["sku"], $eanarray)) {
						$addqty = DB::table('solidarity_counter')->where('property', 'quantity')->increment('value', $order["line_items"][$key]["quantity"]);
						if (isset($addqty)) {
							$qty = DB::table('solidarity_counter')->where('property', 'quantity')->value('value');
							Utilidades::shopifyRESTAPI('PUT', 'metafields/11900677849188.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": ' . $qty . ' } }');
							Utilidades::shopifyRESTAPI('PUT', 'metafields/11646377754685.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": ' . $qty . ' } }');
							Utilidades::shopifyRESTAPI('PUT', 'metafields/11882554359894.json', Utilidades::shopifyStoreSel('PT'), '{ "metafield": {"value": ' . $qty . ' } }');
						}
					}
				}
			}
		}
	}

	public function notifyLocationItemsToKlaviyo(Request $request)
	{
		if (Utilidades::shopifyVerifyWebhook($request)) {

			$order = $request->json()->all();

			if (isset($order["line_items"])) {

				$trigger = false;
				$hasstock = false;
				$skuarray = array();

				foreach ($order["line_items"] as $key => $value) {
					$check = $this->checkLocationStock("14386692141", $order["line_items"][$key]["sku"]);
					if ($check != 0) {
						unset($order["line_items"][$key]);
						$hasstock = true;
					} else {
						$response = Utilidades::shopifyRESTAPI('GET', 'products/' . $order["line_items"][$key]["product_id"] . '.json', Utilidades::shopifyStoreSel('ES'), 'fields=image');
						$order["line_items"][$key]["featured_image"]["url"] = $response["product"]["image"]["src"];
						$skuarray[] = $order["line_items"][$key]["sku"];
						$trigger = true;
					}
				}

				if ($trigger) {

					//Modify order attributes
					$orderid = $order["id"];

					$updateorder = '{
						"order": {
						  "id": ' . $orderid . ',
						  "note_attributes": [
							{
							  "name": "NoStockOnline",
							  "value": "' . implode(",", $skuarray) . '"
							}
						  ]
						}
					}';

					$update = Utilidades::shopifyRESTAPI('PUT', 'orders/' . $orderid . '.json', Utilidades::shopifyStoreSel('ES'), $updateorder);
				}

				if (($trigger) && (!$hasstock)) {
					$cust_properties = [
						'$email' => $order["email"]
					];
	
					$properties = [
						'order_name' => $order["name"],
						'items' => $order["line_items"]
					];
	
					$event_name = 'Pedido Stock Sucursal (NoStockOnline)';
					$kly_request = Utilidades::klaviyoTrackAPI(Utilidades::shopifyStoreSel('ES'), $event_name, json_encode($cust_properties), json_encode($properties));
				}

				$hasstock = false;
				$trigger = false;

			}

		}
	}

	public function notifyLocationItemsToKlaviyoTest(Request $request)
	{
		if (Utilidades::shopifyVerifyWebhook($request)) {

			$order = $request->json()->all();

			if (isset($order["line_items"])) {

				$trigger = false;
				$hasstock = false;
				$skuarray = array();

				foreach ($order["line_items"] as $key => $value) {
					$check = $this->checkLocationStock("14386692141", $order["line_items"][$key]["sku"]);
					if ($check != 0) {
						unset($order["line_items"][$key]);
						$hasstock = true;
					} else {
						$response = Utilidades::shopifyRESTAPI('GET', 'products/' . $order["line_items"][$key]["product_id"] . '.json', Utilidades::shopifyStoreSel('T1'), 'fields=image');
						$order["line_items"][$key]["featured_image"]["url"] = $response["product"]["image"]["src"];
						$skuarray[] = $order["line_items"][$key]["sku"];
						$trigger = true;
					}
				}

				if ($trigger) {

					//Modify order attributes
					$orderid = $order["id"];

					$updateorder = '{
						"order": {
						  "id": ' . $orderid . ',
						  "note_attributes": [
							{
							  "name": "NoStockOnline",
							  "value": "' . implode(",", $skuarray) . '"
							}
						  ]
						}
					}';

					$update = Utilidades::shopifyRESTAPI('PUT', 'orders/' . $orderid . '.json', Utilidades::shopifyStoreSel('T1'), $updateorder);
				}

				if (($trigger) && (!$hasstock)) {
					$cust_properties = [
						'$email' => $order["email"]
					];
	
					$properties = [
						'order_name' => $order["name"],
						'items' => $order["line_items"]
					];
	
					$event_name = 'Pedido Stock Sucursal (NoStockOnline)';
					// $kly_request = Utilidades::klaviyoTrackAPI(Utilidades::shopifyStoreSel('ES'), $event_name, json_encode($cust_properties), json_encode($properties));
				}

				$hasstock = false;
				$trigger = false;

			}

		}
	}
}