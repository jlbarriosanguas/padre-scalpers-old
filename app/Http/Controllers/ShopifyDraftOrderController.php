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

class ShopifyDraftOrderController extends Controller
{
	public function createDraft(Request $request) {
        $draftcustomer["customer"] = $request->all()['customer'];
        $draftcustomer["customer"]["verified_email"] = true;
        $draftcustomer["customer"]["addresses"][0] = $request->all()['address'];

        if ($draftcustomer["customer"]["addresses"][0]["address2"] == null) {
            unset($draftcustomer["customer"]["addresses"][0]["address2"]);
        }

        if ($draftcustomer["customer"]["phone"] != null) {
            $draftcustomer["customer"]["metafields"][0]["key"] = "phone_number";
            $draftcustomer["customer"]["metafields"][0]["value"] = $draftcustomer["customer"]["phone"];
            $draftcustomer["customer"]["metafields"][0]["value_type"] = "string";
            $draftcustomer["customer"]["metafields"][0]["namespace"] = "customr";
        }

        $draftcustomer["customer"]["addresses"][0]["first_name"] = $draftcustomer["customer"]["first_name"];
        $draftcustomer["customer"]["addresses"][0]["last_name"] = $draftcustomer["customer"]["last_name"];
        $draftcustomer["customer"]["addresses"][0]["phone"] = $draftcustomer["customer"]["phone"];
        unset($draftcustomer["customer"]["phone"]);

        //Check Customer -> if exists get id | if not exists create it and return id
        $createcustomer = Utilidades::shopifyRESTAPI('POST', 'customers.json', Utilidades::shopifyStoreSel('ES'), json_encode($draftcustomer, true));
        if (isset($createcustomer["errors"])) {
            $checkcustomer = Utilidades::shopifyRESTAPI('GET', 'customers/search.json?query=email:'.$draftcustomer["customer"]["email"], Utilidades::shopifyStoreSel('ES'), "");
            if ($checkcustomer["customers"] != null) {
                $customerid = $checkcustomer["customers"][0]["id"]; 
            }
        } else {
            $customerid = $createcustomer["customer"]["id"];
        }

        // TODO - Create draft order using cart line-items (var id)
        $lineitems = explode(";", $request->all()['line_items']);
        $draftorder["draft_order"]["line_items"] = [];
        foreach ($lineitems as $key => $value) {
            if ($lineitems[$key] != '') {
                $arr = explode(",", $lineitems[$key]);
                $item = [
                    "variant_id" => $arr[0],
                    "quantity" => $arr[1]
                ];
                array_push($draftorder["draft_order"]["line_items"], $item);
            }
        }
        $draftorder["draft_order"]["customer"]["id"] = $customerid;
        $draftorder["draft_order"]["note"] = "Probador";
        $draftorder["draft_order"]["note_attributes"] = [
            array("name" => "IBAN", "value" => $draftcustomer["customer"]["iban"])
        ];
        $draftorder["draft_order"]["shipping_address"] = $request->all()['address'];
        $draftorder["draft_order"]["shipping_address"]["first_name"] = $draftcustomer["customer"]["addresses"][0]["first_name"];
        $draftorder["draft_order"]["shipping_address"]["last_name"] = $draftcustomer["customer"]["addresses"][0]["last_name"];
        $draftorder["draft_order"]["shipping_address"]["phone"] = $draftcustomer["customer"]["addresses"][0]["phone"];
        $draftorder["draft_order"]["billing_address"] = $draftorder["draft_order"]["shipping_address"];

        // return $draftorder;
        $createdraft = Utilidades::shopifyRESTAPI('POST', 'draft_orders.json', Utilidades::shopifyStoreSel('ES'), json_encode($draftorder, true));
        if (isset($createdraft["draft_order"])) {
            $paydraft = Utilidades::shopifyRESTAPI('PUT', 'draft_orders/' . $createdraft["draft_order"]["id"] . '/complete.json', Utilidades::shopifyStoreSel('ES'), "");
            if (isset($paydraft["draft_order"])) {
                $response = "¡Pedido realizado con éxito!";
            }
        } else {
            $response = "Error al procesar el pedido";
        }
        return $response;
	}
}
