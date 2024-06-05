<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopifyOAuthController extends Controller
{
	public function install(Request $request) {

		if (!isset($_GET['shop'])) {
			exit(json_encode(array("errors" => "Unauthorized access")));
		}

		if (isset($_GET['pwd'])) {
			if ($_GET['pwd'] != env('PADRE_INSTALL_PWD')) {
				exit(json_encode(array("errors" => "Unauthorized access")));
			}
		} else {
			exit(json_encode(array("errors" => "Unauthorized access")));
		}

		$shop = $_GET['shop'];
		$api_key = env('PADRE_API_KEY');
		$scopes = "read_orders,write_products,read_customers,write_customers";
		$redirect_uri = route('generate_token');
		// Build install/approval URL to redirect to
		$install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);
		// Redirect
		header("Location: " . $install_url);
		die();
	}

	public function generateToken(Request $request) {
		if (!isset($_GET['hmac'])) {
			exit(json_encode(array("errors" => "Invalid request")));
		}
		// Set variables for our request
		$api_key = env('PADRE_API_KEY');
		$shared_secret = env('PADRE_API_SECRET');
		$params = $_GET; // Retrieve all request parameters
		$hmac = $_GET['hmac']; // Retrieve HMAC request parameter
		$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
		ksort($params); // Sort params lexographically
		$computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);
		// Use hmac data to check that the response is from Shopify or not
		if (hash_equals($hmac, $computed_hmac)) {
			// Set variables for our request
			$query = array(
				"client_id" => $api_key, // Your API key
				"client_secret" => $shared_secret, // Your app credentials (secret key)
				"code" => $params['code'] // Grab the access key from the URL
			);
			// Generate access token URL
			$access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";
			// Configure curl client and execute request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $access_token_url);
			curl_setopt($ch, CURLOPT_POST, count($query));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
			$result = curl_exec($ch);
			curl_close($ch);
			// Store the access token
			$result = json_decode($result, true);
			$access_token = $result['access_token'];
			// Show the access token (don't do this in production!)
			echo 'Success! Access token: ' . $access_token;
		} else {
			// Someone is trying to be shady!
			die('This request is NOT from Shopify!');
		}
	}
}
