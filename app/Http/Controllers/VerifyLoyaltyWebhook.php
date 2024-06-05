<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyLoyaltyWebhook extends Controller
{
    function verify_webhook(Request $data) {
	  $hmac = $data->header('x-loyaltylion-hmac-sha256');
	  $shopDomain = $data->header('x-loyaltylion-site-domain');
	  $our_hmac = base64_encode(
		hash_hmac("sha256", file_get_contents("php://input"), env('LOYALTYLION_SECRET'), true)
	  );
	  Log::debug($hmac);
	  return response($hmac, "200");
	}
	
}
