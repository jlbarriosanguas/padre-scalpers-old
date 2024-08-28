<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use DateTime;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ShopifyUserController extends Controller
{
	// Extra fields to process from form
	const METAFIELDS = ["birthday_date", "postal_code", "phone_number", "gender", "country"];

	public function createUser(Request $request) {
		// Log::debug(env('SFY_ENABLED_STORES'));
		// Log::debug($request);
		// Definitions
        $customer = $request->all()['customer'];
        if (isset($request->all()['interest'])) {
            $interests = $request->all()['interest'];
        }
        $originurl = str_replace('https://', '', $request->headers->get('origin'));
        $enabledstores = "EMTEST,T1,ES,EU,UK,WW,FR,PT,RO,MX,DE,CO,CL,BE";
        $response = '';

        // Origin Control
        $check = $this->checkOrigin($enabledstores, $originurl);
        if (empty($check)) {
            return response('Forbidden', 403)->header('Content-Type', 'text/plain');
        } else {
            $origincode = $check;
        }

        // Phone number space trimming
        if (isset($customer['phone_number'])) {
            $customer['phone_number'] = str_replace(' ', '', $customer['phone_number']);
        }

        // Base User
        $user = [
            "customer" => [
                "first_name" => $customer['first_name'],
                "last_name" => $customer['last_name'],
                "password" => $customer['password'],
                "password_confirmation" => $customer['password'],
                "email" => $customer['email'],
                "tags" =>  [],
                "metafields" => []
            ]
        ];
        // Klaviyo properties
        $properties = [
            'email' => $customer['email'],
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name']
        ];

		if (isset($request->all()['fullForm'])) {

			if ($request->all()['fullForm'] == "false") {
				array_push($user["customer"]["tags"], "nofid");
			} else {
                // Extra User Fields
				foreach ($customer as $name => $value) {
					if (in_array($name, self::METAFIELDS) && $value != null) {
						array_push($user["customer"]["metafields"], [
							"key" => $name,
							"value" => $value,
							"type" => "string",
							"namespace" => "customr"
						]);
						switch ($name) {
                            case "birthday_date":
                                try {
                                    $time2 = new DateTime($value);
                                    $properties['birthday_shopify'] = $time2->format('Y-m-d H:i:s');
                                } catch(\Exception $e) {
                                    //Log::warning($e->getMessage());
                                }
								break;
							case "zip":
								$properties["postal_code"] = $value;
								break;
							case "phone_number":
								$properties['phone_number'] = $value;
								break;
							case "gender":
								$properties["gender"] = $value;
								break;
							case "country":
								$properties['country'] = $value;
								break;
						}
					}
				}
			}


		}

        if (isset($customer['accepts_marketing'])) {
            $user["customer"]["accepts_marketing"] = $customer['accepts_marketing'];
            $properties["Accepts Marketing"] = $customer['accepts_marketing'];
            // Interests
            if ($customer['accepts_marketing'] == true && isset($interests)) {
                $properties["InteresesNewsletter"] = [];
				// Log::debug($interests);
                foreach ($interests as $name => $value) {
                    array_push($user["customer"]["metafields"], [
                        "key" => 'interest_'.$name,
                        "value" => $value,
                        "type" => "string",
                        "namespace" => "customr"
                    ]);
                    switch ($name) {
                        case 'man':
                            array_push($properties["InteresesNewsletter"], "Int_Hombre");
                            break;
                        case 'woman':
                            array_push($properties["InteresesNewsletter"], "Int_Mujer");
                            break;
                        case 'kids':
                            array_push($properties["InteresesNewsletter"], "Int_Kids");
                            break;
						case 'ib':
                            array_push($properties["InteresesNewsletter"], "Int_InvitedBrands");
                            break;
						case 'home':
                            array_push($properties["InteresesNewsletter"], "Int_Home");
                            break;
                    }
                }
            }
        }

        if (isset($request->all()['company']['name'])) {
            array_push($user["customer"]["tags"], "Corporate");
            array_push($user["customer"]["tags"], "Company: " . $request->all()['company']['name']);
        }

		// Disabled user check and invite
		$sfy_customer = Utilidades::shopifyRESTAPI('GET', '/admin/api/2023-01/customers/search.json?query=email:'.$customer['email'], Utilidades::shopifyStoreSel($origincode), "");

		if ($sfy_customer["customers"] != null) {
			// TAGS para customers en formulario personalizado
            if ($request->all()['fullForm'] == "true") {
				// Log::debug($request['customer']['id_klv_list']);
				// array_push($user["customer"]["tags"], "POLARSOUND24");
				if(isset($request['customer']['id_klv_list'])) {
					$client = new \GuzzleHttp\Client(['verify' => false]);

					switch ($request->customer['country']){
						case "ES":
							$apiKey = env('KLY_ES_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_ES_PUBLIC_API_KEY');
							break;
						case "PT":
							$apiKey = env('KLY_PT_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_PT_PUBLIC_API_KEY');
							break;
						case "FR":
							$apiKey = env('KLY_FR_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_FR_PUBLIC_API_KEY');
							break;
						case "UK":
							$apiKey = env('KLY_UK_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_UK_PUBLIC_API_KEY');
							break;
						case "EU":
							$apiKey = env('KLY_EU_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_EU_PUBLIC_API_KEY');
							break;
						case "WW":
							$apiKey = env('KLY_WW_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_WW_PUBLIC_API_KEY');
							break;
						case "DE":
							$apiKey = env('KLY_DE_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_DE_PUBLIC_API_KEY');
							break;
						case "CL":
							$apiKey = env('KLY_CL_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_CL_PUBLIC_API_KEY');
							break;
						case "MX":
							$apiKey = env('KLY_MX_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_MX_PUBLIC_API_KEY');
							break;
						case "CO":
							$apiKey = env('KLY_CO_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_CO_PUBLIC_API_KEY');
							break;
						case "BE":
							$apiKey = env('KLY_BE_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_BE_PUBLIC_API_KEY');
							break;
					}
					
					$response = $client->request('POST', 'https://a.klaviyo.com/client/profiles/?company_id=' . $publicApiKey, [
					  'body' => '{"data":{"type":"profile","attributes":{"email":"' . $request['customer']['email'] . '","first_name":"' . $request['customer']['first_name'] . '","last_name":"' . $request['customer']['last_name'] . '"}}}',
					  'headers' => [
						'accept' => 'application/json',
						'content-type' => 'application/json',
						'revision' => '2023-12-15',
					  ],
					]);

					/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/people/search?email=' . $request['customer']['email'] . '&api_key=' . $apiKey, [
					  'headers' => [
						'accept' => 'application/json',
					  ],
					]);*/
					$client = new \GuzzleHttp\Client(['verify' => false]);
					
					Log::debug("api key->");
					Log::debug($apiKey);
					

					$response = $client->request('GET', 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"' . $request['customer']['email'] . '")', [
					  'headers' => [
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'accept' => 'application/json',
						'revision' => '2024-06-15',
					  ],
					]);
					
					$idKlaviyoUser = json_decode($response->getBody(), true);
					$idKlaviyoUser = $idKlaviyoUser['data'][0]['id'];
					
					/*$userKlaviyo = $client->request('GET', 'https://a.klaviyo.com/api/profiles/' . $idKlaviyoUser, [
					  'headers' => [
						'accept' => 'application/json',
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'revision' => '2023-02-22',
					  ],
					]);*/
					
					$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/' . $request['customer']['id_klv_list'] . '/relationships/profiles/', [
					  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
					  'headers' => [
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'accept' => 'application/json',
						'content-type' => 'application/json',
						'revision' => '2023-12-15',
					  ],
					]);
				}
				array_push($user["customer"]["tags"], "tier: Bronze");
			}

            if ($sfy_customer["customers"][0]["state"] == "disabled") {
                $invite = Utilidades::shopifyRESTAPI('POST', '/admin/api/2023-01/customers/' . $sfy_customer["customers"][0]["id"] . '/send_invite.json', Utilidades::shopifyStoreSel($origincode), '{ "customer_invite": {} }');
                if (isset($invite["customer_invite"])) {
                    // Update customer fields after sending the invite and before returning status
                    $cust_array = $request->customer;
                    $cust_array["id"] = $sfy_customer["customers"][0]["id"];
                    $request->merge(["customer" => $cust_array]);
                    $this->updateUser($request);
					// Utilidades::klaviyoIdentifyApi(Utilidades::shopifyStoreSel($origincode), json_encode($properties, true));
				}
				return $invite;
            }
        } else {

            // Create user
            // TAGS para customers en formulario personalizado
            if ($request->all()['fullForm'] == "true") {

				// Log::debug(json_decode($request['customer']['id_klv_list']));
				// array_push($user["customer"]["tags"], "POLARSOUND24");
				if(isset($request['customer']['id_klv_list'])){

					$client = new \GuzzleHttp\Client(['verify' => false]);

					switch ($request->customer['country']){
						case "ES":
							$apiKey = env('KLY_ES_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_ES_PUBLIC_API_KEY');
							break;
						case "PT":
							$apiKey = env('KLY_PT_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_PT_PUBLIC_API_KEY');
							break;
						case "FR":
							$apiKey = env('KLY_FR_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_FR_PUBLIC_API_KEY');
							break;
						case "UK":
							$apiKey = env('KLY_UK_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_UK_PUBLIC_API_KEY');
							break;
						case "EU":
							$apiKey = env('KLY_EU_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_EU_PUBLIC_API_KEY');
							break;
						case "WW":
							$apiKey = env('KLY_WW_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_WW_PUBLIC_API_KEY');
							break;
						case "DE":
							$apiKey = env('KLY_DE_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_DE_PUBLIC_API_KEY');
							break;
						case "CL":
							$apiKey = env('KLY_CL_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_CL_PUBLIC_API_KEY');
							break;
						case "MX":
							$apiKey = env('KLY_MX_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_MX_PUBLIC_API_KEY');
							break;
						case "CO":
							$apiKey = env('KLY_CO_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_CO_PUBLIC_API_KEY');
							break;
						case "BE":
							$apiKey = env('KLY_BE_PRIVATE_API_KEY');
							$publicApiKey = env('KLY_BE_PUBLIC_API_KEY');
							break;
					}
					
					$response = $client->request('POST', 'https://a.klaviyo.com/client/profiles/?company_id=' . $publicApiKey, [
					  'body' => '{"data":{"type":"profile","attributes":{"email":"' . $request['customer']['email'] . '","first_name":"' . $request['customer']['first_name'] . '","last_name":"' . $request['customer']['last_name'] . '","properties":' . json_encode($properties, true) . '}}}',
					  'headers' => [
						'accept' => 'application/json',
						'content-type' => 'application/json',
						'revision' => '2023-12-15',
					  ],
					]);

					/*$response = $client->request('GET', 'https://a.klaviyo.com/api/v2/people/search?email=' . $request['customer']['email'] . '&api_key=' . $apiKey, [
					  'headers' => [
						'accept' => 'application/json',
					  ],
					]);*/
					$client = new \GuzzleHttp\Client(['verify' => false]);

					$response = $client->request('GET', 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"' . $request['customer']['email'] . '")', [
					  'headers' => [
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'accept' => 'application/json',
						'revision' => '2024-06-15',
					  ],
					]);
					/*$client = new Client();
					$headers = [
					  'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
					  'revision' => '2024-05-15'
					];
					$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request['customer']['email'] . '")', $headers);
					$response = $client->sendAsync($request)->wait();*/
					$idKlaviyoUser = json_decode($response->getBody(), true);
					$idKlaviyoUser = $idKlaviyoUser['data'][0]['id'];
					
					/*$userKlaviyo = $client->request('GET', 'https://a.klaviyo.com/api/profiles/' . $idKlaviyoUser, [
					  'headers' => [
						'accept' => 'application/json',
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'revision' => '2023-02-22',
					  ],
					]);*/		
					$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/' . $request['customer']['id_klv_list'] . '/relationships/profiles/', [
					  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
					  'headers' => [
						'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
						'accept' => 'application/json',
						'content-type' => 'application/json',
						'revision' => '2023-12-15',
					  ],
					]);
				}
				
                array_push($user["customer"]["tags"], "tier: Bronze");
			}
            $sfy_customer = Utilidades::shopifyRESTAPI('POST', '/admin/api/2023-01/customers.json', Utilidades::shopifyStoreSel($origincode), json_encode($user, true));
            if (isset($sfy_customer["customer"])) {
                // Notify to Klaviyo
                // Utilidades::klaviyoIdentifyApi(Utilidades::shopifyStoreSel($origincode), json_encode($properties, true));
            }
        }

		// if(isset($request['customer']['accepts_marketing']) && $request['customer']['accepts_marketing'] && $customer['country'] == "ES") {
			// Utilidades::klaviyoAddProfileToList($customer['email']);
		// }
		// Utilidades::klaviyoAddProfileToList();

		// Loyalty solo está ES
        if($customer["country"] == "ES"){
			if (isset($sfy_customer["customer"])) {
				$n = 0;
				do {
					$lty_customer = Utilidades::getLoyaltyCustomer($customer["email"], env('LTY_API_KEY'), env('LTY_API_PWD'));
					// Log::debug($lty_customer);
					$n++;
				} while(!isset($lty_customer["customers"][0]));
				Utilidades::updateLoyaltyUserBirthday($customer["email"], $lty_customer["customers"][0]["merchant_id"], $customer["birthday_date"], env('LTY_API_KEY'), env('LTY_API_PWD'));
			}
		}

		return $sfy_customer;
	}

	// Update fields of a existent user
    public function updateUser(Request $request) {
        // Definitions
        $customer = $request->all()['customer'];
        $originurl = str_replace('https://', '', $request->headers->get('origin'));
        $enabledstores = env('SFY_ENABLED_STORES');
        $currentmetafields = [];
        $response = '';

        // Origin Control
        $check = $this->checkOrigin($enabledstores, $originurl);
        if (empty($check)) {
            return response('Forbidden', 403)->header('Content-Type', 'text/plain');
        } else {
            $origincode = $check;
        }

        // Phone number space trimming
        if (isset($customer['phone_number'])) {
            $customer['phone_number'] = str_replace(' ', '', $customer['phone_number']);
        }

        // Check if Customer ID is in the request
        if (!isset($customer['id'])) {
            return response('Missing ID', 400)->header('Content-Type', 'text/plain');
        }

        // Getting customer object and associated metafields array
        $sfy_customer_full = Utilidades::shopifyRESTAPI('GET', 'customers/' . $customer['id'] . '.json', Utilidades::shopifyStoreSel($origincode), "");
        if (isset($sfy_customer_full["errors"])) {
            if ($sfy_customer_full["errors"] == 'Not Found') {
                return response('Customer not found', 404)->header('Content-Type', 'text/plain');
            }
        } else {
            $sfy_customer_metafields = Utilidades::shopifyRESTAPI('GET', 'customers/' . $customer['id'] . '/metafields.json', Utilidades::shopifyStoreSel($origincode), "namespace=customr");
        }

        $sfy_customer_tags =  explode(", ", $sfy_customer_full["customer"]["tags"]);

		if (isset($sfy_customer_tags)) {
            $nofid_check = in_array("nofid", $sfy_customer_tags);
            $nofid2_check = in_array("tier: No Fidelizado", $sfy_customer_tags);
			if ($nofid_check || $nofid2_check){
				$key = array_search('nofid', $sfy_customer_tags);
				unset($sfy_customer_tags[$key]);
				$key2 = array_search('tier: No Fidelizado', $sfy_customer_tags);
				unset($sfy_customer_tags[$key2]);
				array_push($sfy_customer_tags, "tier: Bronze");
			}
        }

        if (isset($customer["tags"])) {
            foreach ($customer["tags"] as $tag) {
                if ($tag == "Corporate") {
                    array_push($sfy_customer_tags, $tag);
                }
                if (strpos($tag, 'Company:') !== false) {
                    array_push($sfy_customer_tags, $tag);
                }
            }
        }

        // Klaviyo properties
        $properties = [
            '$email' => $sfy_customer_full['customer']['email']
        ];

        // Base User
        $user = [
			"customer" => [
				"id" => $customer['id'],
                "email" => $sfy_customer_full['customer']['email'],
                "tags" => array_values($sfy_customer_tags),
				"metafields" => []
			]
        ];

        if (isset($customer['first_name'])) {
            $user["customer"]["first_name"] = $customer['first_name'];
        }

        if (isset($customer['last_name'])) {
            $user["customer"]["last_name"] = $customer['last_name'];
        }

        if (isset($customer['postal_code'])) {
            $properties["postal_code"] = $customer['postal_code'];
        }

        if (isset($customer['phone_number'])) {
            $properties['phone_number'] = $customer['phone_number'];
        }

        if (isset($customer['gender'])) {
            $properties['gender'] = $customer['gender'];
        }

        if (isset($customer['birthday_date'])) {
            try {
                $time2 = new DateTime($customer['birthday_date']);
                $properties['birthday_shopify'] = $time2->format('Y-m-d H:i:s');
            } catch(\Exception $e) {
                //Log::warning($e->getMessage());
            }
        }

        if (isset($sfy_customer_metafields["metafields"])) {

            // Replace existing values if field value is different
            foreach ($sfy_customer_metafields["metafields"] as $key => $value) {
                $metafield = $sfy_customer_metafields["metafields"][$key]["key"];
                $value = $sfy_customer_metafields["metafields"][$key]["value"];
                $namespace = $sfy_customer_metafields["metafields"][$key]["namespace"];
                if (isset($customer[$metafield])) {
                    if (in_array($metafield, self::METAFIELDS) && $value != $customer[$metafield]) {
                        $updatemeta = Utilidades::shopifyRESTAPI('PUT', 'metafields/' . $sfy_customer_metafields["metafields"][$key]["id"] . '.json', Utilidades::shopifyStoreSel($origincode), '{ "metafield": {"value": "' . $customer[$metafield] . '" } }');
                    }
                }
                if ($namespace == 'customr') {
                    array_push($currentmetafields, $metafield);
                }
            }

            // Append new metafields to Base User if they don't exist already
            foreach ($customer as $name => $value) {
                if (in_array($name, self::METAFIELDS) && !in_array($name, $currentmetafields) && $value != null) {
                    if ($name == "birthday_date") {
                        $birth_already_exist = true;
                    }
                    array_push($user["customer"]["metafields"], [
                        "key" => $name,
                        "value" => $value,
                        "type" => "string",
                        "namespace" => "customr"
                    ]);
                }
            }
        }

        // Update User
        $update = Utilidades::shopifyRESTAPI('PUT', 'customers/' . $customer['id'] . '.json', Utilidades::shopifyStoreSel($origincode), json_encode($user, true));

        // Utilidades::klaviyoIdentifyApi(Utilidades::shopifyStoreSel($origincode), json_encode($properties, true));
		
		$client = new \GuzzleHttp\Client(['verify' => false]);

		switch ($origincode){
			case "ES":
				$apiKey = env('KLY_ES_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_ES_PUBLIC_API_KEY');
				break;
			case "PT":
				$apiKey = env('KLY_PT_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_PT_PUBLIC_API_KEY');
				break;
			case "FR":
				$apiKey = env('KLY_FR_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_FR_PUBLIC_API_KEY');
				break;
			case "UK":
				$apiKey = env('KLY_UK_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_UK_PUBLIC_API_KEY');
				break;
			case "EU":
				$apiKey = env('KLY_EU_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_EU_PUBLIC_API_KEY');
				break;
			case "WW":
				$apiKey = env('KLY_WW_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_WW_PUBLIC_API_KEY');
				break;
			case "DE":
				$apiKey = env('KLY_DE_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_DE_PUBLIC_API_KEY');
				break;
			case "CL":
				$apiKey = env('KLY_CL_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_CL_PUBLIC_API_KEY');
				break;
			case "MX":
				$apiKey = env('KLY_MX_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_MX_PUBLIC_API_KEY');
				break;
			case "CO":
				$apiKey = env('KLY_CO_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_CO_PUBLIC_API_KEY');
				break;
			case "BE":
				$apiKey = env('KLY_BE_PRIVATE_API_KEY');
				$publicApiKey = env('KLY_BE_PUBLIC_API_KEY');
				break;
		}
		
		$body = '{"data":{"type":"profile","attributes":{"email":"' . $sfy_customer_full['customer']['email'] . '","phone_number":"' . $customer['phone_number'] . '","first_name":"' . $customer['first_name'] . '","last_name":"' . $customer['last_name'] . '","properties":' . json_encode($properties, true) . '}}}';

		$response = $client->request('POST', 'https://a.klaviyo.com/client/profiles/?company_id=' . $publicApiKey, [
		  'body' => $body,
		  'headers' => [
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-12-15',
		  ],
		]);
        
		if ($origincode == 'ES') {
			do {
				$lty_customer = Utilidades::getLoyaltyCustomer($sfy_customer_full['customer']['email'], env('LTY_API_KEY'), env('LTY_API_PWD'));
			} while(!isset($lty_customer["customers"][0]));
			Utilidades::updateLoyaltyUserBirthday($sfy_customer_full['customer']['email'], $lty_customer["customers"][0]["merchant_id"], $customer["birthday_date"], env('LTY_API_KEY'), env('LTY_API_PWD'));
		}
		
        return $update;
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
	
	/*
	* Se ejecuta cuando accedemos a la sección de newsletter y lo que hace es decirnos consultando en klaviyo si el usuario existe y dar la información.
	*/
	public function getKlaviyoCustomer(Request $request) {
		$client = new \GuzzleHttp\Client(['verify' => false]);

		switch ($request->store){
			case "ES":
				$apiKey = env('KLY_ES_PRIVATE_API_KEY');
				break;
			case "PT":
				$apiKey = env('KLY_PT_PRIVATE_API_KEY');
				break;
			case "FR":
				$apiKey = env('KLY_FR_PRIVATE_API_KEY');
				break;
			case "UK":
				$apiKey = env('KLY_UK_PRIVATE_API_KEY');
				break;
			case "EU":
				$apiKey = env('KLY_EU_PRIVATE_API_KEY');
				break;
			case "WW":
				$apiKey = env('KLY_WW_PRIVATE_API_KEY');
				break;
			case "DE":
				$apiKey = env('KLY_DE_PRIVATE_API_KEY');
				break;
			case "CL":
				$apiKey = env('KLY_CL_PRIVATE_API_KEY');
				break;
			case "MX":
				$apiKey = env('KLY_MX_PRIVATE_API_KEY');
				break;
			case "CO":
				$apiKey = env('KLY_CO_PRIVATE_API_KEY');
				break;
			case "BE":
				$apiKey = env('KLY_BE_PRIVATE_API_KEY');
				break;
		}

		$response = $client->request('GET', 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"' . $request->mail . '")' , [
			'headers' => [
			  'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
			  'accept' => 'application/json',
			  'revision' => '2024-05-15',
			],
		]);
		
		$idKlaviyoUser = json_decode($response->getBody(), true);
		
		if (isset($idKlaviyoUser['data'][0])) {
			$idKlaviyoUser = $idKlaviyoUser['data'][0]['id'];
		} else {
			$idKlaviyoUser = $idKlaviyoUser['data']['id'];
		}
		
		$userKlaviyo = $client->request('GET', 'https://a.klaviyo.com/api/profiles/' . $idKlaviyoUser, [
		  'headers' => [
			'accept' => 'application/json',
			'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
			'revision' => '2023-02-22',
		  ],
		]);
		
		$userKlaviyo = json_decode($userKlaviyo->getBody(), true);
		
		$consent = $userKlaviyo['data']['attributes']['subscriptions']['email']['marketing']['consent'];
		
		return $consent;
	}
	
	public function addProfileToList(Request $request, String $email, String $first_name, String $last_name, String $date_submit, String $birthday_shopify, String $consentDisney, String $consent) {
		$client = new \GuzzleHttp\Client(['verify' => false]);
		
		$apiKey = env('KLY_ES_PRIVATE_API_KEY');
		$publicApiKey = env('KLY_ES_PUBLIC_API_KEY');
		
		$response = $client->request('POST', 'https://a.klaviyo.com/client/profiles/?company_id=' . $publicApiKey, [
		  'body' => '{"data":{"type":"profile","attributes":{"email":"' . $email . '","first_name":"' . $first_name . '","last_name":"' . $last_name . '","properties":{"date_submit_disney":"' . $date_submit . '", "birthday_shopify":"' . $birthday_shopify . '", "consent_disney":"' . $consentDisney . '"}}}}',
		  'headers' => [
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-12-15',
		  ],
		]);
				
		$client = new Client();
		$headers = [
		  'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
		  'revision' => '2024-05-15'
		];
		$request = new Request('GET', 'https://a.klaviyo.com/api/profiles?filter=equals(email,"' . $request->mail . '")', $headers);
		$response = $client->sendAsync($request)->wait();
		
		$idKlaviyoUser = json_decode($response->getBody(), true);
		$idKlaviyoUser = $idKlaviyoUser['id'];
		
		/*$userKlaviyo = $client->request('GET', 'https://a.klaviyo.com/api/profiles/' . $idKlaviyoUser, [
		  'headers' => [
			'accept' => 'application/json',
			'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
			'revision' => '2023-02-22',
		  ],
		]);*/
		
		$response = $client->request('POST', 'https://a.klaviyo.com/api/lists/UU9GmC/relationships/profiles/', [
		  'body' => '{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '"}]}',
		  'headers' => [
			'Authorization' => 'Klaviyo-API-Key ' . env('KLY_ES_PRIVATE_API_KEY'),
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-12-15',
		  ],
		]);
		
		if ($consent === "true") {		
			$response = $client->request('POST', 'https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs/', [
			  'body' => '{"data":{"type":"profile-subscription-bulk-create-job","attributes":{"custom_source":"Marketing Event","profiles":{"data":[{"type":"profile","id":"' . $idKlaviyoUser . '","attributes":{"email":"' . $email . '","subscriptions":{"email":{"marketing":{"consent":"SUBSCRIBED","consented_at":"' . date("Y-m-d") . 'T' . date("H:i:s") . '"}}}}}]}},"relationships":{"list":{"data":{"type":"list","id":"UU9GmC"}}}}}',
			  'headers' => [
				'Authorization' => 'Klaviyo-API-Key ' . env('KLY_ES_PRIVATE_API_KEY'),
				'accept' => 'application/json',
				'content-type' => 'application/json',
				'revision' => '2024-02-15',
			  ],
			]);
		}
				
		return "ok";
	}
	
	public function getStock() {
		return "OK";
	}

}