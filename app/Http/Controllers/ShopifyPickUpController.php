<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use App\Location;

class ShopifyPickUpController extends Controller
{
    public function storeLocation(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required',
            'name' => 'required',
            'address1' => 'required',
            'city' => 'required',
            'country' => 'required',
            'phone' => 'required'
        ]);

        $goo_add1 = str_replace(" ","+",$request->input('address1'));
        $goo_add2 = str_replace(" ","+",$request->input('address2'));
        (null == $goo_add2) ? $goo_add = $goo_add1 : $goo_add = $goo_add1 . '+' . str_replace(" ","+",$goo_add2);

        $geocode = Utilidades::googleGeocoding($goo_add . '+' . $request->input('city'), $request->input('country_code'));

        return $geocode;

        if (isset($geocode["results"]) && $geocode["status"] == 'OK') {
            $components = $geocode["results"][0]["address_components"];
            $longitude = $geocode["results"][0]["geometry"]["location"]["lng"];
            $latitude = $geocode["results"][0]["geometry"]["location"]["lat"];
            foreach ($components as $key => $value) {
                if ($components[$key]["types"][0] == "country") {
                    $country_name = $components[$key]["long_name"];
                    $country_code = $country = $components[$key]["short_name"];
                }
                if ($components[$key]["types"][0] == "postal_code") {
                    $zip = $components[$key]["long_name"];
                }
                if ($components[$key]["types"][0] == "administrative_area_level_2") {
                    $province = $components[$key]["long_name"];
                    // $province_code = $components[$key]["short_name"];
                    $province_code = self::getProvinceCode($request->input('country'), $province);
                }
            }
        } else {
            $longitude = null;
            $latitude = null;
        }

        $location = new Location([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'address1' => $request->input('address1'),
            'address2' => $request->input('address2'),
            'city' => $request->input('city'),
            'zip' => $zip,
            'province' => $province,
            'province_code' => $province_code,
            'country' => $country,
            'country_code' => $country_code,
            'country_name' => $country_name,
            'phone' => $request->input('phone'),
            'longitude' => $longitude,
            'latitude' => $latitude,
        ]);

        $store_offer = $location->save();

        if ($store_offer) {
            $response = 'Location stored';
        } else {
            $response = 'Error while storing values';
        }

        return $response;
    }

    private static function getProvinceCode($country, $province) {
        // ISO 3166-2:ES
        if ($country == 'ES' || $country == 'España' || $country == 'Spain') {
            switch ($province) {
                case 'A Coruña':
                case 'La Coruña':
                    $province_code = 'C';
                    break;
                case 'Araba':
                case 'Álava':
                    $province_code = 'VI';
                    break;
                case 'Albacete':
                    $province_code = 'AB';
                    break;
                case 'Alacant':
                case 'Alicante':
                    $province_code = 'A';
                    break;
                case 'Almería':
                    $province_code = 'AL';
                    break;
                case 'Asturias':
                    $province_code = 'O';
                    break;
                case 'Ávila':
                    $province_code = 'AV';
                    break;
                case 'Badajoz':
                    $province_code = 'BA';
                    break;
                case 'Balears':
                case 'Illes Balears':
                case 'Baleares':
                    $province_code = 'PM';
                    break;
                case 'Barcelona':
                    $province_code = 'B';
                    break;
                case 'Bizkaia':
                case 'Vizcaya':
                    $province_code = 'BI';
                    break;
                case 'Burgos':
                    $province_code = 'BU';
                    break;
                case 'Cáceres':
                    $province_code = 'CC';
                    break;
                case 'Cádiz':
                    $province_code = 'CA';
                    break;
                case 'Cantabria':
                    $province_code = 'S';
                    break;
                case 'Castelló':
                case 'Castellón':
                    $province_code = 'CS';
                    break;
                case 'Ciudad Real':
                    $province_code = 'CR';
                    break;
                case 'Córdoba':
                    $province_code = 'CO';
                    break;
                case 'Cuenca':
                    $province_code = 'CU';
                    break;
                case 'Guipuzkoa':
                case 'Guipúzcoa':
                    $province_code = 'SS';
                    break;
                case 'Girona':
                    $province_code = 'GI';
                    break;
                case 'Granada':
                    $province_code = 'GR';
                    break;
                case 'Guadalajara':
                    $province_code = 'GU';
                    break;
                case 'Huelva':
                    $province_code = 'H';
                    break;
                case 'Huesca':
                    $province_code = 'HU';
                    break;
                case 'Jaén':
                    $province_code = 'J';
                    break;
                case 'La Rioja':
                    $province_code = 'LO';
                    break;
                case 'Las Palmas':
                    $province_code = 'GC';
                    break;
                case 'León':
                    $province_code = 'LE';
                    break;
                case 'Lleida':
                case 'Lérida':
                    $province_code = 'L';
                    break;
                case 'Lugo':
                    $province_code = 'LU';
                    break;
                case 'Madrid':
                    $province_code = 'M';
                    break;
                case 'Málaga':
                    $province_code = 'MA';
                    break;
                case 'Murcia':
                    $province_code = 'MU';
                    break;
                case 'Nafarroa':
                case 'Navarra':
                    $province_code = 'NA';
                    break;
                case 'Ourense':
                case 'Orense':
                    $province_code = 'OR';
                    break;
                case 'Palencia':
                    $province_code = 'P';
                    break;
                case 'Pontevedra':
                    $province_code = 'PO';
                    break;
                case 'Salamanca':
                    $province_code = 'SA';
                    break;
                case 'Santa Cruz de Tenerife':
                    $province_code = 'TF';
                    break;
                case 'Segovia':
                    $province_code = 'SG';
                    break;
                case 'Sevilla':
                    $province_code = 'SE';
                    break;
                case 'Soria':
                    $province_code = 'SO';
                    break;
                case 'Tarragona':
                    $province_code = 'T';
                    break;
                case 'Teruel':
                    $province_code = 'TE';
                    break;
                case 'Toledo':
                    $province_code = 'TO';
                    break;
                case 'València':
                case 'Valencia':
                    $province_code = 'V';
                    break;
                case 'Valladolid':
                    $province_code = 'VA';
                    break;
                case 'Zamora':
                    $province_code = 'ZA';
                    break;
                case 'Zaragoza':
                    $province_code = 'Z';
                    break;
            }
        }
        return $province_code;
    }

    // public function storeLocation(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'title' => 'required',
    //         'summernote' => 'required',
    //         'department' => 'required',
    //         'city' => 'required'
    //     ]);

    //     $offer = new JobOffer([
    //         'offer_status' => 'active',
    //         'title' => $request->get('title'),
    //         'body' => $request->get('summernote'),
    //         'department' => $request->get('department'),
    //         'city' => $request->get('city'),
    //         'observations' => $request->get('observations'),
    //         //'image' => $request->get('image')
    //     ]);
    //     $store_offer = $offer->save();

    //     if ($store_offer) {
    //         $response = 'Job Offer created';
    //     } else {
    //         $response = 'Error while storing values';
    //     }

    //     return $response;
    // }
}
