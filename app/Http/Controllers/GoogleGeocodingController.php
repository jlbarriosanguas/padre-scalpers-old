<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class GoogleGeocodingController extends Controller
{
    public function getGeocoding(Request $request, $address, $country_code)
	{
        if (isset($address) && isset($country_code)){
            return Utilidades::googleGeocoding($address, $country_code);
        } else {
            return response('Missing params', 400)->header('Content-Type', 'text/plain');
        }
    }
}
