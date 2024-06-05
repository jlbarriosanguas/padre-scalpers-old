<?php

namespace App\Http\Controllers;

use App\Jobs\ShopifyInterestUpdateJob;
use Illuminate\Support\Facades\Log;

class UpdateMailPreferences extends Controller
{
    // Actualiza los intereses del cliente en las tiendas Shopify
    // definidas en SFY_ENABLED_STORES.
    public function updateShopifyInterests()
    {
        // Comprobamos si recibimos el parámetro con la información
        if (!isset($_GET["data"])) {
            // Notificamos parámetro faltante
            $response = response()->json(['error' => 'Missing person data']);
        } else {
            // Lanzamos Job y pasamos la info. Notificamos estado.
            $decoded = json_decode(base64_decode($_GET["data"]), true);

            //$this->dispatch(new ShopifyInterestUpdateJob($decoded));
            //$response = response()->json([
            //    'job_status' => 'CREATED',
            //    'message' => 'Updating customer interests on enabled stores',
			//   'enabled_stores' => env('SFY_ENABLED_STORES'),
            //    'customer_email' => $decoded["email"],
            // ]);

             if (!isset($decoded["email"]) || !isset($decoded["interest_man"]) || !isset($decoded["interest_woman"]) || !isset($decoded["interest_kids"]) || !isset($decoded["interest_invitedbrands"]) || !isset($decoded["interest_home"])) {
                 return response()->json(['error' => 'Incomplete person data']);
             } else {
                 $this->dispatch(new ShopifyInterestUpdateJob($decoded));
                 $response = response()->json([
                     'job_status' => 'CREATED',
                     'message' => 'Updating customer interests on enabled stores',
                     'enabled_stores' => env('SFY_ENABLED_STORES'),
                     'customer_email' => $decoded["email"],
                  ]);
             }
        }
        return $response;
    }
}
