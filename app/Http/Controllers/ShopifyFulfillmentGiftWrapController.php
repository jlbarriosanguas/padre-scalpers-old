<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyFulfillmentGiftWrapController extends Controller
{
	public function giftwrapFulfillment(Request $request) {
		if (Utilidades::shopifyVerifyWebhook($request)) {
			//$request = json_decode($request, TRUE);
			$fulfilled_line_items = [];
			$cancelled_line_items = [];
			$store_code = Utilidades::storeSelector('ES');
				foreach($request['line_items'] as $line_item) {
					if ($line_item != null) {
						if ($line_item['sku'] == '8445279830118' && $line_item['fulfillment_status'] == null) {
							Log::debug("PEDIDO CON PACKAGING DE REGALO: " . $request['id']);
							$data = [
								"fulfillment" => [
									"line_items_by_fulfillment_order"  => [[
										"fulfillment_order_id" => "",
										"fulfillment_order_line_items" => [[
											"id" => $line_item['id'],
											"quantity" => $line_item['quantity']
										]]
									]],
									"notify_customer" => false,
									// "tracking_info" => [
									// 	"company" => $tracking_company,
									// 	"number" => $tracking_number,
									// 	"url" => $tracking_url
									// ]
								]
							];
							if ($data) {
								$response = Utilidades::createShopifyFulfillment($store_code, $request['id'], json_encode($data));
								Log::debug($response);
							}
						}
					}
				}
		}
	}
}
