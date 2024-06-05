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
use Illuminate\Support\Facades\Log;

class LoyaltyCustomEventsController extends Controller
{
    public function appDownload(Request $request) {

        // Definitions
        $customer = $request->all()['customer'];
        $originurl = str_replace('https://', '', $request->headers->get('origin'));
        $enabledstores = env('SFY_ENABLED_STORES');
        $lty_api_key = env('LTY_API_KEY');
        $lty_api_pwd = env('LTY_API_PWD');
        $response = '';

        // Origin Control
        $check = $this->checkOrigin($enabledstores, $originurl);
        if (empty($check)) {
            return response('Forbidden', 403)->header('Content-Type', 'text/plain');
        } else {
            $origincode = $check;
        }

        // Request Body
        $data = [
            "name" => "descarga_app",
            "customer_id" => $customer["lty_id"],
            "customer_email" => $customer["email"],
            "state" => "approved"
        ];

        // Creamos nuevo cliente Guzzle con la URI base de la API de Loyaltylion
		$ly_client = new \GuzzleHttp\Client(['base_uri' => 'https://api.loyaltylion.com/']);

        try {
            $request = $ly_client->post("v2/activities", [
                'auth' => [ $lty_api_key, $lty_api_pwd ],
                'headers' => [ 'Content-Type' => 'application/json' ],
                'json' => $data
            ]);
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $error = $e->getResponse()->getBody();
            $errorcode = $e->getResponse()->getStatusCode();
            return response($error, $errorcode)->header('Content-Type', 'text/plain');
        }

        // Output (HTTP Status + plain text message)
        return response($request->getBody(), $request->getStatusCode())->header('Content-Type', 'text/plain');
    }

    public function mediumSurvey(Request $request) {

        // Definitions
        $customer = $request->all()['customer'];
        $originurl = str_replace('https://', '', $request->headers->get('origin'));
        $enabledstores = env('SFY_ENABLED_STORES');
        $lty_api_key = env('LTY_API_KEY');
        $lty_api_pwd = env('LTY_API_PWD');
        $response = '';

        // Origin Control
        $check = $this->checkOrigin($enabledstores, $originurl);
        if (empty($check)) {
            return response('Forbidden', 403)->header('Content-Type', 'text/plain');
        } else {
            $origincode = $check;
        }

        // Request Body
        $data = [
            "name" => "encuesta_medios",
            "customer_id" => $customer["lty_id"],
            "customer_email" => $customer["email"],
            "state" => "approved"
        ];

        // Creamos nuevo cliente Guzzle con la URI base de la API de Loyaltylion
		$ly_client = new \GuzzleHttp\Client(['base_uri' => 'https://api.loyaltylion.com/']);

        try {
            $request = $ly_client->post("v2/activities", [
                'auth' => [ $lty_api_key, $lty_api_pwd ],
                'headers' => [ 'Content-Type' => 'application/json' ],
                'json' => $data
            ]);
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $error = $e->getResponse()->getBody();
            $errorcode = $e->getResponse()->getStatusCode();
            return response($error, $errorcode)->header('Content-Type', 'text/plain');
        }

        // Output (HTTP Status + plain text message)
        return response($request->getBody(), $request->getStatusCode())->header('Content-Type', 'text/plain');
    }

    public function invitedBrandsSurvey(Request $request) {

        // Definitions
        $customer = $request->all()['customer'];
        $originurl = str_replace('https://', '', $request->headers->get('origin'));
        $enabledstores = env('SFY_ENABLED_STORES');
        $lty_api_key = env('LTY_API_KEY');
        $lty_api_pwd = env('LTY_API_PWD');
        $response = '';

        // Origin Control
        $check = $this->checkOrigin($enabledstores, $originurl);
        if (empty($check)) {
            return response('Forbidden', 403)->header('Content-Type', 'text/plain');
        } else {
            $origincode = $check;
        }

        // Request Body
        $data = [
            "name" => "encuesta_invited_brands",
            "customer_id" => $customer["lty_id"],
            "customer_email" => $customer["email"],
            "state" => "approved"
        ];

        // Creamos nuevo cliente Guzzle con la URI base de la API de Loyaltylion
		$ly_client = new \GuzzleHttp\Client(['base_uri' => 'https://api.loyaltylion.com/']);

        try {
            $request = $ly_client->post("v2/activities", [
                'auth' => [ $lty_api_key, $lty_api_pwd ],
                'headers' => [ 'Content-Type' => 'application/json' ],
                'json' => $data
            ]);
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $error = $e->getResponse()->getBody();
            $errorcode = $e->getResponse()->getStatusCode();
            return response($error, $errorcode)->header('Content-Type', 'text/plain');
        }

        // Output (HTTP Status + plain text message)
        return response($request->getBody(), $request->getStatusCode())->header('Content-Type', 'text/plain');
    }
	
	public function rewardsPadreVogue(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new \GuzzleHttp\Client();

			$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/TgscYR/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreAd(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new \GuzzleHttp\Client();

			$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/THXJQJ/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreVf(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new \GuzzleHttp\Client();

			$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/WgEbw7/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreGq(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new \GuzzleHttp\Client();

			$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/TTPvzS/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreBrooklyn(Request $request) {
		Log::debug("rewardsPadreBrooklyn");
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {
			Log::debug("rewardsPadreBrooklyn1");
			$client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
		  $client = new \GuzzleHttp\Client();
			// Lista de Brooklyn
			Log::debug("lista brooklyn");
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/QXAJ2y/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);	

			/*$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/QXAJ2y/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);*/
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsRevolutGold(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {	
			$client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
		  $client = new \GuzzleHttp\Client();
		  // Lista de RevolutGold
			Log::debug("lista RevolutGold");
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/XLPHqf/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);

			/*$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/XLPHqf/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);*/
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsRevolutBronze(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {	
			$client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];		
		  $client = new \GuzzleHttp\Client();
		  // Lista de RevolutGold
			Log::debug("lista RevolutGold");
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/UgQsL8/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);

			/*$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/UgQsL8/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);*/
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreSynergym(Request $request) {
		Log::debug("lista Synergym");
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {	  
		  /*$client = new \GuzzleHttp\Client();

			$response = $client->request('POST', 'https://a.klaviyo.com/api/v2/list/WL2EgX/members?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
			  'body' => '{"profiles":[{"email":"' . $request['customer_email'] . '"}]}',
			  'headers' => [
				'accept' => 'application/json',
				'content-type' => 'application/json',
			  ],
			]);*/
			
			$client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
			
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/WL2EgX/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreBobbibrown(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
			
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/WL2EgX/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadrePolardSound(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
			
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/WL2EgX/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function rewardsPadreTraveler(Request $request) {
		$verified = self::verify_webhook(
		  file_get_contents("php://input"),
		  $_SERVER["HTTP_X_LOYALTYLION_HMAC_SHA256"]
		);
		if ($verified) {		  
		  $client = new Client();
			$headers = [
			  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			  'revision' => '2024-05-15'
			];
			$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
			$response = $client->sendAsync($request)->wait();
			$idKlaviyoUser = json_decode($response->getBody(), true);
			$idKlaviyoUser = $idKlaviyoUser['id'];
			
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/WL2EgX/relationships/profiles/', [
			  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-05-15',
			  ],
			]);
		  
		  return response("OK", 200);
		}
		return response("FORBIDDEN", 302);
	}
	
	public function verify_webhook($data, $hmac) {
	  $our_hmac = base64_encode(
		hash_hmac("sha256", $data, env('LTY_API_PWD'), true)
	  );
	  return $our_hmac == $hmac;
	}
	
	public function getPicSubscription($mail) {
		
		$client = new Client();
		$headers = [
		  'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
		  'revision' => '2024-05-15'
		];
		$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer_email'] . '")', $headers);
		$response = $client->sendAsync($request)->wait();
		$idKlaviyoUser = json_decode($response->getBody(), true);
		$idKlaviyoUser = $idKlaviyoUser['id'];
		
		// Lista de Vogue
		//$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/TgscYR/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		//  'headers' => [
		//	'accept' => 'application/json',
		//  ],
		//]);
		//$listVogue = json_decode($response->getBody(), true);
		//foreach ($listVogue['records'] as $customer) {
		//	foreach($customer as $key => $value) {
		//		if ($value == $mail) {
		//			return response("Suscrito en la lista", 200);
		//		}
		//	}
        //}
		
		// Lista de AD
		//$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/THXJQJ/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		//  'headers' => [
		//	'accept' => 'application/json',
		//  ],
		//]);
		//$listVogue = json_decode($response->getBody(), true);
		//foreach ($listVogue['records'] as $customer) {
		//	foreach($customer as $key => $value) {
		//		if ($value == $mail) {
		//			return response("Suscrito en la lista", 200);
		//		}
		//	}
        //}
		
		// Lista de VF
		//$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/WgEbw7/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		//  'headers' => [
		//	'accept' => 'application/json',
		//  ],
		//]);
		//$listVogue = json_decode($response->getBody(), true);
		//foreach ($listVogue['records'] as $customer) {
		//	foreach($customer as $key => $value) {
		//		if ($value == $mail) {
		//			return response("Suscrito en la lista", 200);
		//		}
		//	}
        //}
		
		// Lista de GQ
		//$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/TTPvzS/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		//  'headers' => [
		//	'accept' => 'application/json',
		//  ],
		//]);
		//$listVogue = json_decode($response->getBody(), true);
		//foreach ($listVogue['records'] as $customer) {
		//	foreach($customer as $key => $value) {
		//		if ($value == $mail) {
		//			return response("Suscrito en la lista", 200);
		//		}
		//	}
        //}
		
		// Lista de Traveler
		//$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/X5wYRS/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		//  'headers' => [
		//	'accept' => 'application/json',
		//  ],
		//]);
		//$listVogue = json_decode($response->getBody(), true);
		//foreach ($listVogue['records'] as $customer) {
		//	foreach($customer as $key => $value) {
		//		if ($value == $mail) {
		//			return response("Suscrito en la lista", 200);
		//		}
		//	}
        //}
		
		$resultado = [];
		$client = new \GuzzleHttp\Client();
		// Lista de Brooklyn
		Log::debug("lista brooklyn");
		$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/QXAJ2y/relationships/profiles/', [
		  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
		  'headers' => [
			'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2024-05-15',
		  ],
		]);		
		/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/QXAJ2y/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		  'headers' => [
			'accept' => 'application/json',
		  ],
		]);*/
		$listBrooklyn = json_decode($response->getBody(), true);
		foreach ($listBrooklyn['records'] as $customer) {
			foreach($customer as $key => $value) {
				if ($value == $mail) {
					array_push($resultado, "Suscrito en la lista Brooklyn");
				}
			}
        }
		
		// Lista de Synergym
		$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/WL2EgX/relationships/profiles/', [
		  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
		  'headers' => [
			'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2024-05-15',
		  ],
		]);	
		/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/WL2EgX/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		  'headers' => [
			'accept' => 'application/json',
		  ],
		]);*/
		$listSynergym = json_decode($response->getBody(), true);
		foreach ($listSynergym['records'] as $customer) {
			foreach($customer as $key => $value) {
				if ($value == $mail) {
					array_push($resultado, "Suscrito en la lista Synergym");
				}
			}
        }
		
		// Lista de Bobbibrown
		$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/UVWEyM/relationships/profiles/', [
		  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
		  'headers' => [
			'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2024-05-15',
		  ],
		]);	
		/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/UVWEyM/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		  'headers' => [
			'accept' => 'application/json',
		  ],
		]);*/
		$listBobbibrown = json_decode($response->getBody(), true);
		foreach ($listBobbibrown['records'] as $customer) {
			foreach($customer as $key => $value) {
				if ($value == $mail) {
					array_push($resultado, "Suscrito en la lista Bobbibrown");
				}
			}
        }
		
		// Lista de PolarSound
		$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/UVWEyM/relationships/profiles/', [
		  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
		  'headers' => [
			'Authorization' => 'Klaviyo-API-Key pk_9c947a2522cf6299e6b305991894c6df68',
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2024-05-15',
		  ],
		]);	
		/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/group/VGY2Zm/members/all?api_key=pk_9c947a2522cf6299e6b305991894c6df68', [
		  'headers' => [
			'accept' => 'application/json',
		  ],
		]);*/
		$listBobbibrown = json_decode($response->getBody(), true);
		foreach ($listBobbibrown['records'] as $customer) {
			foreach($customer as $key => $value) {
				if ($value == $mail) {
					array_push($resultado, "Suscrito en la lista PolarSound");
				}
			}
        }
		
		
		return ($resultado);
		
	}

	

    private function checkOrigin($storeurlarray, $originurl) {
        $origincode = '';
        foreach (explode(',', $storeurlarray) as $storecode) {
            if ((env('SFY_' . $storecode . '_STORE_URL') == $originurl) || (env('SFY_' . $storecode . '_STORE_DOMAIN') == $originurl)) {
                $origincode = $storecode;
                break;
            }
        }
        return $origincode;
    }
}