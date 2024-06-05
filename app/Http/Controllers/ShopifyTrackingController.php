<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;

class ShopifyTrackingController extends Controller
{
	public function index($id)
    {
		$call = Utilidades::getSEURExpeditionInfo($id);
		return $call;
	}

	public function test($id)
    {
		$call = Utilidades::getAftershipExpeditionInfo($id);
		return $call;
	}

}