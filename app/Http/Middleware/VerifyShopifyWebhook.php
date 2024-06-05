<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyShopifyWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get request headers
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        // $apiVersion = $request->header('X-Shopify-API-Version');

        // Get store from config
        foreach (config('app.shopify') as $key => $info) {
            if ($shopDomain === $info['domain']) {
                $store = $info;
            }
        }

        if (isset($store)) {
            if ($store['webhook_token']) {
                // Ingregrity check
                $calculatedHmac = base64_encode(hash_hmac(
                    'sha256',
                    file_get_contents('php://input'),
                    $store['webhook_token'],
                    true
                ));
                $response = (!hash_equals($hmac, $calculatedHmac)) ?
                    response()->json([
                        "status" => "error",
                        "message" => "Failed integrity check"
                    ], 400) : $next($request);
            } else {
                response()->json([
                    "status" => "error",
                    "message" => "Missing store info"
                ], 404);
            }
        } else {
            // Store not found
            response()->json([
                "status" => "error",
                "message" => "Origin store not found"
            ], 404);
        }

        return $response;
    }
}
