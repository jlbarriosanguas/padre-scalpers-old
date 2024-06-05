<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;


class ShopifyFeedController extends Controller
{
	public function generateFeed($merchant, $sfy_store)
    {
		if (!isset($merchant))
		{
			throw new \Exception('Unexpecified merchant');
			//exit(json_encode(array("errors" => "Unexpecified merchant")));
		}
		if ($merchant != 'facebook')
		{
			throw new \Exception('Invalid merchant');
			//exit(json_encode(array("errors" => "Invalid merchant")));
		}

		if (!isset($sfy_store))
		{
			throw new \Exception('Unexpecified Shopify store');
			//exit(json_encode(array("errors" => "Unexpecified Shopify store")));
		}

		if ($sfy_store == 'es' || $sfy_store == 'eu' || $sfy_store == 'uk' || $sfy_store == 'pt' || $sfy_store == 'fr' || $sfy_store == 'ww' || $sfy_store == 'mx' || $sfy_store == 'de' || $sfy_store == 'cl' || $sfy_store == 't1')
		{
			$sfy_store_mayus = strtoupper($sfy_store);
		}
		else
		{
			throw new \Exception('Invalid Shopify store');
			//exit(json_encode(array("errors" => "Invalid Shopify store")));
		}

		$time_start = microtime(true);

		$filter = 'published_status:published';
		$withoutKids = false;
		$onlySilhouettes = false;

		if (isset($_GET["options"])){
			$options = json_decode(base64_decode($_GET["options"]), true);
			if ($options["withoutStock"] == "false") {
				$filter = $filter.' inventory_total:>0';
			}
			if (isset($options["withoutKids"])){
				if ($options["withoutKids"] == "true") {
					$withoutKids = true;
				}
			}
			if (isset($options["onlySilhouettes"])){
				if ($options["onlySilhouettes"] == "true") {
					$onlySilhouettes = true;
				}
			}
		}

		$store = Utilidades::shopifyStoreSel($sfy_store_mayus);

		$query = '{
			products(query:"' . $filter . '") {
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
				  priceRange {
				    maxVariantPrice{
					  amount
					  currencyCode
				    }
				    minVariantPrice{
					  amount
					  currencyCode
				    }
				  }
				  featuredImage {
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
						selectedOptions {
							name
							value
						}
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

		//$fileNameJsonl = time() . '_products_feed_export.jsonl';
		$fileNameJsonl = 'cache_products_feed_export.jsonl';
		Storage::put(('/public/jsonl/'.$fileNameJsonl), '');
		Storage::put(('/public/jsonl/'.$fileNameJsonl), $products);
		$product = '';

		$jsonl = Storage::get('/public/jsonl/'.$fileNameJsonl);
		$handle = fopen("../storage/app/public/jsonl/".$fileNameJsonl, "r");

		$prod_array = [];

		do
		{
			$jsonl = fgets($handle);
			if ($jsonl != '')
			{

				$decode = json_decode($jsonl, true);
				$skip = false;

				if (strpos($decode["id"], "/Product/") !== false) //Main
				{
					if (isset($decode["tags"]) && $withoutKids == true) {
						foreach($decode["tags"] as $tag => $value)
						{
							switch($decode["tags"][$tag])
							{
								case 'Niño':
								case 'Niña':
									$skip = true;
									break;
								default:
									break;
							}
						}
					}

					if ($skip) {
						continue;
					}

					(isset($decode["tags"]) ? $decode["tags"] = implode(',', $decode["tags"]) : '');
					(isset($decode["featuredImage"]) ? $decode["featuredImage"] = $decode["featuredImage"]["originalSrc"] : '');

					if (isset($decode["priceRange"]))
					{
						$decode["minPrice"] = $decode["priceRange"]["minVariantPrice"]["amount"];
						$decode["maxPrice"] = $decode["priceRange"]["maxVariantPrice"]["amount"];
						$decode["currency"] = $decode["priceRange"]["minVariantPrice"]["currencyCode"];
					}

					array_push($prod_array, $decode);

				}
				else if (strpos($decode["id"], "/ProductImage/") !== false) //Images
				{
					foreach ($prod_array as $key => $value)
					{
						if ($prod_array[$key]["id"] == $decode["__parentId"])
						{
							if (!isset($prod_array[$key]["images"]))
							{
								$prod_array[$key]["images"] = $decode["originalSrc"];
							}
							else
							{
								$prod_array[$key]["images"] = $prod_array[$key]["images"].','.$decode["originalSrc"];
							}
						}
					}
				}
				else if (strpos($decode["id"], "/ProductVariant/") !== false) //Variants
				{
					foreach ($prod_array as $key => $value) {
						if ($prod_array[$key]["id"] == $decode["__parentId"])
						{
							if (!isset($prod_array[$key]["sku"]))
							{
								$prod_array[$key]["sku"] = $decode["sku"];
								$prod_array[$key]["price"] = $decode["price"];
								$prod_array[$key]["compareAtPrice"] = $decode["compareAtPrice"];
								if (isset($decode["selectedOptions"]))
								{
									foreach ($decode["selectedOptions"] as $keyb => $value)
									{
										$keyname = $decode["selectedOptions"][$keyb]["name"];
										$prod_array[$key][$keyname] = $decode["selectedOptions"][$keyb]["value"];
									}
								}
							}

						}
					}
				}
				else if (strpos($decode["id"], "/Collection/") !== false) //Collections
				{
					foreach ($prod_array as $key => $value)
					{
						if ($prod_array[$key]["id"] == $decode["__parentId"])
						{
							if (!isset($prod_array[$key]["collections"]))
							{
								$prod_array[$key]["collections"] = $decode["title"];
							}
							else
							{
								$prod_array[$key]["collections"] = $prod_array[$key]["collections"].','.$decode["title"];
							}
						}
					}
				}
			}

		} while(!feof($handle));

		fclose($handle);

		switch($merchant)
		{
			case 'facebook':
				$fileNameXml = 'fb_' . $sfy_store;

				if ($withoutKids == true)
				{
					$fileNameXml .= '_nokids';
				}

				if ($onlySilhouettes == true)
				{
					$fileNameXml .= '_sil';
				}

				$fileNameXml .= '_feed.xml';

				$feed_url = 'https://padre.scalpers.es/feeds/facebook/'.$fileNameXml;
				$this->facebookRSSXml($prod_array, $sfy_store, $fileNameXml, $feed_url, $onlySilhouettes);
				break;
			default:
				break;
		}

		$time_end = (microtime(true) - $time_start).' s';
		$peak_mem = (memory_get_peak_usage(true)/1024/1024)." MB";

		$end = [ 'status' => 'COMPLETED', 'objectCount' => sizeof($prod_array), "feedUrl" => $feed_url, 'processTime' => $time_end, 'peakMemoryUsage' => $peak_mem];
		return response()->json($end);

	}

	public function facebookRSSXml($prod_array, $sfy_store, $fileNameXml, $feed_url, $onlySilhouettes)
    {
		// Debugger: https://business.facebook.com/ads/product_feed/debug

		Storage::disk('feeds')->put(('facebook/'.$fileNameXml), '');
		$xmlhandle = fopen("../public/feeds/facebook/".$fileNameXml, "w");

		$xml_ver = '1.0';
		$xml_encoding = 'utf-8';
		$rss_ver = '2.0';
		//$feed_xmlns = "http://www.w3.org/2005/Atom";
		$feed_xmlns_g = "http://base.google.com/ns/1.0";

		fputs($xmlhandle, '<?xml version="' . $xml_ver . '" encoding="' . $xml_encoding . '"?>\n');
		fputs($xmlhandle, '<rss version="' . $rss_ver . '" xmlns:g="' . $feed_xmlns_g . '">\n');
		fputs($xmlhandle, '<channel>\n');
		//fputs($xmlhandle, '<title>Shopify ' . strtoupper($sfy_store) . ' Product Feed</title>');
		//fputs($xmlhandle, '<description>Facebook product feed by Atom specifications.</description>');
		//fputs($xmlhandle, '<link>https://' . env("SFY_".strtoupper($sfy_store)."_STORE_DOMAIN") . '</link>');
		//fputs($xmlhandle, '<atom:link href="' . $feed_url . '" rel="self" type="application/rss+xml" />');

		// EAN array for dupe validation. Sort of 'hacky'.
		$ean_array = [];

		foreach ($prod_array as $key => $value)
		{
			if (isset($prod_array[$key]["sku"]))
			{
				if (in_array($prod_array[$key]["sku"], $ean_array)) {
					continue;
				}
			}

			// Product
			fputs($xmlhandle, '<item>');

			// (isset($prod_array[$key]["id"]) ? fputs($xmlhandle, '<g:id><![CDATA[' . str_replace("gid://shopify/Product/","",$prod_array[$key]["id"]) . ']]></g:id>') : '');
			// (isset($prod_array[$key]["id"]) ? fputs($xmlhandle, '<g:productID><![CDATA[' . str_replace("gid://shopify/Product/","",$prod_array[$key]["id"]) . ']]></g:productID>') : '');

			if (isset($prod_array[$key]["sku"]))
			{
				array_push($ean_array, $prod_array[$key]["sku"]);
				fputs($xmlhandle, '<g:gtin><![CDATA[' . $prod_array[$key]["sku"] . ']]></g:gtin>\n');
			}

			if (isset($prod_array[$key]["title"]))
			{
				$camel_title = mb_convert_case($prod_array[$key]["title"], MB_CASE_TITLE, 'UTF-8');
				$trans = array(
					" Ii" => " II",
					" Iii" => " III",
					" Bd " => " BD ",
					" Bt " => " BT ",
					" Se " => " SE ",
					" Rp " => " RP ",
					" Be " => " BE ",
					" Nl " => " NL ",
				);
				$camel_title = strtr($camel_title, $trans);

				fputs($xmlhandle, '<g:title><![CDATA[' . $camel_title . ']]></g:title>\n');
				//fputs($xmlhandle, '<title_org><![CDATA[' . $prod_array[$key]["title"] . ']]></title_org>');
			}

			if (isset($prod_array[$key]["description"]))
			{
				fputs($xmlhandle, '<g:description><![CDATA[' . $prod_array[$key]["description"] . ']]></g:description>\n');
			}

			if (isset($prod_array[$key]["handle"]))
			{
				fputs($xmlhandle, '<g:link><![CDATA[https://' . env("SFY_".strtoupper($sfy_store)."_STORE_DOMAIN") . '/products/' . $prod_array[$key]["handle"] . ']]></g:link>\n');
			}

			if (isset($prod_array[$key]["featuredImage"]))
			{
				if ($onlySilhouettes && isset($prod_array[$key]["images"])) {
					$img_array = explode(",", $prod_array[$key]["images"]);
					$silhouetteUrl = null;

					foreach ($img_array as $keyimg => $url)
					{
						if (strpos($url, "-S.jpg") === true || strpos($url, "-S_") === true) {
							$silhouetteUrl = $url;
						}
					}

					if ($silhouetteUrl != null) {
						fputs($xmlhandle, '<g:image_link><![CDATA[' . $silhouetteUrl . ']]></g:image_link>\n');
					} else {
						fputs($xmlhandle, '<g:image_link><![CDATA[' . $prod_array[$key]["featuredImage"] . ']]></g:image_link>\n');
					}

				} else {
					fputs($xmlhandle, '<g:image_link><![CDATA[' . $prod_array[$key]["featuredImage"] . ']]></g:image_link>\n');
				}
			}

			if (!$onlySilhouettes) {
				if (isset($prod_array[$key]["images"]))
				{
					$img_array = explode(",", $prod_array[$key]["images"]);
					foreach ($img_array as $keyimg => $value)
					{
						fputs($xmlhandle, '<additional_image_link><![CDATA[' . $img_array[$keyimg] . ']]></additional_image_link>\n');
					}
				}
			}

			if (isset($prod_array[$key]["vendor"]))
			{
				fputs($xmlhandle, '<g:brand><![CDATA[' . $prod_array[$key]["vendor"] . ']]></g:brand>\n');
			}

			fputs($xmlhandle, '<g:condition><![CDATA[New]]></g:condition>\n');

			if (isset($prod_array[$key]["totalInventory"]))
			{
				if ($prod_array[$key]["totalInventory"] > 0)
				{
					fputs($xmlhandle, '<g:availability><![CDATA[in stock]]></g:availability>\n');
				}
				else
				{
					fputs($xmlhandle, '<g:availability><![CDATA[out of stock]]></g:availability>\n');
				}
			}

			if (isset($prod_array[$key]["productType"]))
			{
				fputs($xmlhandle, '<g:product_type><![CDATA[' . $prod_array[$key]["productType"] . ']]></g:product_type>\n');
			}

			/* Old prices
			if (isset($prod_array[$key]["priceRange"]))
			{
				fputs($xmlhandle, '<g:price><![CDATA[' . ($prod_array[$key]["maxPrice"]/100) . ' ' . $prod_array[$key]["currency"] .']]></g:price>');
				fputs($xmlhandle, '<g:sale_price><![CDATA[' . ($prod_array[$key]["minPrice"]/100) . ' ' .$prod_array[$key]["currency"] .']]></g:sale_price>');
			}
			*/

			if (isset($prod_array[$key]["price"]))
			{
				if (isset($prod_array[$key]["compareAtPrice"])) {
					fputs($xmlhandle, '<g:price><![CDATA[' . ($prod_array[$key]["compareAtPrice"]) . ' ' . $prod_array[$key]["currency"] .']]></g:price>\n');
					if ($prod_array[$key]["compareAtPrice"] > $prod_array[$key]["price"]) {
						fputs($xmlhandle, '<g:sale_price><![CDATA[' . ($prod_array[$key]["price"]) . ' ' .$prod_array[$key]["currency"] .']]></g:sale_price>\n');
					}
				} else {
					fputs($xmlhandle, '<g:price><![CDATA[' . ($prod_array[$key]["price"]) . ' ' . $prod_array[$key]["currency"] .']]></g:price>\n');
				}
			}

			if (isset($prod_array[$key]["tags"]))
			{
				$tags_array = explode(",", $prod_array[$key]["tags"]);
				foreach($tags_array as $keytag => $value)
				{
					if(preg_match('/^\d{5}$/', $tags_array[$keytag])) {
						fputs($xmlhandle, '<g:item_group_id><![CDATA[SKU-' . $tags_array[$keytag] . ']]></g:item_group_id>\n');
						if (isset($prod_array[$key]["Color"])) {
							$color = str_replace(' ', '', $prod_array[$key]["Color"]); // Replaces all spaces with hyphens.
							$color = preg_replace('/[^A-Za-z0-9\-]/', '_', $color); // Removes special chars.
							fputs($xmlhandle, '<g:id><![CDATA[SKU-' . $tags_array[$keytag] . '-' . $color . ']]></g:id>\n');
						} else {
							fputs($xmlhandle, '<g:id><![CDATA[SKU-' . $tags_array[$keytag] . ']]></g:id>\n');
						}
					}

					if (strpos($tags_array[$keytag], "feed-gender-") !== false)
					{
						fputs($xmlhandle, '<g:gender><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:gender>\n');
					}

					/* Original tag-base age_group rule
					if (strpos($tags_array[$keytag], "feed-agegroup-") !== false)
					{
						$agegroup = substr(strrchr($tags_array[$keytag], '-'), 1);
						fputs($xmlhandle, '<g:age_group><![CDATA[' . str_replace("adults", "adult", $agegroup) . ']]></g:age_group>');
					}
					*/

					if (!isset($asigned_age)) {
						switch($tags_array[$keytag])
						{
							case 'Niño':
							case 'Niña':
								fputs($xmlhandle, '<g:age_group><![CDATA[kids]]></g:age_group>\n');
								$asigned_age = true;
								break;
							case 'Hombre':
							case 'Mujer':
								fputs($xmlhandle, '<g:age_group><![CDATA[adult]]></g:age_group>\n');
								$asigned_age = true;
								break;
							default:
								break;
						}
					}

					if (strpos($tags_array[$keytag], "feed-gpc-") !== false)
					{
						fputs($xmlhandle, '<g:google_product_category><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:google_product_category>\n');
					}
					if (strpos($tags_array[$keytag], "feed-cl0") !== false)
					{
						fputs($xmlhandle, '<g:custom_label_0><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:custom_label_0>\n');
					}
					if (strpos($tags_array[$keytag], "feed-cl1") !== false)
					{
						fputs($xmlhandle, '<g:custom_label_1><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:custom_label_1>\n');
					}
					if (strpos($tags_array[$keytag], "feed-cl2") !== false)
					{
						fputs($xmlhandle, '<g:custom_label_2><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:custom_label_2>\n');
					}
					if (strpos($tags_array[$keytag], "feed-cl3") !== false)
					{
						fputs($xmlhandle, '<g:custom_label_3><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:custom_label_3>\n');
					}

					/* Original Custom Label 4 Rule
					if (strpos($tags_array[$keytag], "feed-cl4") !== false)
					{
						fputs($xmlhandle, '<g:custom_label_4><![CDATA[' . substr(strrchr($tags_array[$keytag], '-'), 1) . ']]></g:custom_label_4>');
					}
					*/

					if ($tags_array[$keytag] == "promociones")
					{
						fputs($xmlhandle, '<g:custom_label_4><![CDATA[' . $tags_array[$keytag] . ']]></g:custom_label_4>\n');
					}
				}
			}

			// Destroy stored age_group status
			unset($asigned_age);

			fputs($xmlhandle, '</item>\n');
		}

		fputs($xmlhandle, '</channel>\n');
		fputs($xmlhandle, '</rss>\n');
		$prod_array = [];
		$ean_array = [];
		fclose($xmlhandle);
	}
}
