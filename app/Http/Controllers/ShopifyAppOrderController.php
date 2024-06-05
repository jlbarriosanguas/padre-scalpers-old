<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ShopifyAppOrderController extends Controller
{
    public function receiver(Request $request)
	{
		if (Utilidades::shopifyVerifyWebhook($request)) {

            $order = $request->json()->all();

            if (isset($order["app_id"]) && isset($order["email"])) {
                if ($order["app_id"] == 1520611) {// TapCart

                    $cust_properties = [
                        '$email' => $order["email"]
                    ];

                    $properties = [
                        'Origen' => 'Tapcart',
                        'AppID' => $order["app_id"],
                        'Pedido' => $order["name"]
                    ];

                    $event_name = 'Pedido APP';
                    $kly_request = Utilidades::klaviyoTrackAPI(Utilidades::shopifyStoreSel('ES'), $event_name, json_encode($cust_properties), json_encode($properties));
                    $insert_origin = DB::table('shopify_cart')->where('id', 1)->update(['request_data' => $order["app_id"]]);
                }
            }
		}
	}
}
