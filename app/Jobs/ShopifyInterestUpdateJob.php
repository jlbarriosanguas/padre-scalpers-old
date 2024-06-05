<?php

namespace App\Jobs;

use App\Http\Controllers\Utilidades;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ShopifyInterestUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $startTime = microtime(true);

        $response = ["customer_email" => $this->data["email"], "execution_time" => 0, "target_stores" => []];
        $iteration = 0;

        // Consultamos el cliente en todas las tiendas de Shopify usando su email
        foreach (explode(',', env('SFY_ENABLED_STORES')) as $store) {
            $response["target_stores"][$iteration]["store_code"] = $store;
            $request = Utilidades::retrieveShopifyCustomerByEmail(Utilidades::storeSelector($store), $this->data["email"]);
            $customer = json_decode($request, true)["customers"];

            // Comprobamos si el cliente existe
            if (!$customer) {
                // El cliente no existe en la tienda objetivo. Saltamos iteración.
                $response["target_stores"][$iteration]["update_status"] = 'CUSTOMER_NOT_FOUND';
            } else {
                // Consultamos los metacampos asociados al cliente
                $customerId = $customer[0]["id"];
                $customerMetafields = Utilidades::retrieveShopifyCustomerMetafields(Utilidades::storeSelector($store), $customerId);
                $metafieldArray = json_decode($customerMetafields, true)["metafields"];

                $man = $woman = $kids = $invitedbrands = $home = false;
                $createMetafieldsArray = [];

                // Comprobamos la existencia de los intereses. Si existe un metacampo relacionado,
                // marcar su valor (true/false, string) y almacenar su ID.
                if ($metafieldArray) {
                    foreach ($metafieldArray as $metafield) {
                        switch ($metafield["key"]) {
                            case "interest_man":
                                $man = $this->data["interest_man"];
                                $manId = $metafield["id"];
                                break;
                            case "interest_woman":
                                $woman = $this->data["interest_woman"];
                                $womanId = $metafield["id"];
                                break;
                            case "interest_kids":
                                $kids = $this->data["interest_kids"];
                                $kidsId = $metafield["id"];
                                break;
							case "interest_ib":
								if (!isset($this->data["interest_invitedbrands"])) {
									$this->data["interest_invitedbrands"] = "false";
								}
                                $invitedbrands = $this->data["interest_invitedbrands"];
                                $invitedbrandsId = $metafield["id"];
                                break;
							case "interest_home":
								if (!isset($this->data["interest_home"])) {
									$this->data["interest_home"] = "false";
								}
                                $home = $this->data["interest_home"];
                                $homeId = $metafield["id"];
                                break;
                        }
                    }
                }

                // Incluir metafields no existentes en el array de creación.
                // Actualizar metacampos si ya existen.

                // Interés Hombre
                if (!$man) {
                    array_push($createMetafieldsArray, [
                        "key" => "interest_man",
                        "value" => $this->data["interest_man"],
                        "type" => "string",
                        "namespace" => "customr",
                    ]);
                } else {
                    $data = [
                        "metafield" => [
                            "id" => $manId,
                            "value" => $this->data["interest_man"],
                        ],
                    ];
                    Utilidades::updateShopifyMetafield(Utilidades::storeSelector($store), $manId, json_encode($data));
                }

                // Interés Mujer
                if (!$woman) {
                    array_push($createMetafieldsArray, [
                        "key" => "interest_woman",
                        "value" => $this->data["interest_woman"],
                        "type" => "string",
                        "namespace" => "customr",
                    ]);
                } else {
                    $data = [
                        "metafield" => [
                            "id" => $womanId,
                            "value" => $this->data["interest_woman"],
                        ],
                    ];
                    Utilidades::updateShopifyMetafield(Utilidades::storeSelector($store), $womanId, json_encode($data));
                }

                // Interés Niños
                if (!$kids) {
                    array_push($createMetafieldsArray, [
                        "key" => "interest_kids",
                        "value" => $this->data["interest_kids"],
                        "type" => "string",
                        "namespace" => "customr",
                    ]);
                } else {
                    $data = [
                        "metafield" => [
                            "id" => $kidsId,
                            "value" => $this->data["interest_kids"],
                        ],
                    ];
                    Utilidades::updateShopifyMetafield(Utilidades::storeSelector($store), $kidsId, json_encode($data));
                }
				
				// Interés Invited brands
                if (!$invitedbrands) {
                    array_push($createMetafieldsArray, [
                        "key" => "interest_ib",
                        "value" => $this->data["interest_invitedbrands"],
                        "type" => "string",
                        "namespace" => "customr",
                    ]);
                } else {
                    $data = [
                        "metafield" => [
                            "id" => $invitedbrandsId,
                            "value" => $this->data["interest_invitedbrands"],
                        ],
                    ];
                    Utilidades::updateShopifyMetafield(Utilidades::storeSelector($store), $invitedbrandsId, json_encode($data));
                }
				
				// Home
                if (!$home) {
                    array_push($createMetafieldsArray, [
                        "key" => "interest_home",
                        "value" => $this->data["interest_home"],
                        "type" => "string",
                        "namespace" => "customr",
                    ]);
                } else {
                    $data = [
                        "metafield" => [
                            "id" => $homeId,
                            "value" => $this->data["interest_home"],
                        ],
                    ];
                    Utilidades::updateShopifyMetafield(Utilidades::storeSelector($store), $homeId, json_encode($data));
                }
				
                // Lanzamos petición para creación de metacampos si el array de creación no está vacio.
                if (!empty($createMetafieldsArray)) {
                    $data = [
                        "customer" => [
                            "id" => $customerId,
                            "metafields" => $createMetafieldsArray,
                        ],
                    ];
                    Utilidades::updateShopifyCustomer(Utilidades::storeSelector($store), $customerId, json_encode($data));
                }

                $response["target_stores"][$iteration]["update_status"] = 'OK';

                // DEBUG
                // return Utilidades::retrieveShopifyCustomerMetafields(Utilidades::storeSelector($store), $customerId);
            }

            $iteration++;
        }

        $endTime = microtime(true);
        $response["execution_time"] = $endTime - $startTime;
		
		//Log::debug($response);

        // Laravel 5.6
        // Log::channel('interests_job')->info(json_encode($response));

        Log::useDailyFiles(storage_path().'/logs/jobs/interests_update.log');
        // Log::info(json_encode($response));
        return;
    }
}
