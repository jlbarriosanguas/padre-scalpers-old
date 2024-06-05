<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;

class CorreosCLPostalCodeController extends Controller
{
	public function getCLPostalCode($comuna, $address)
    {
		$call = Utilidades::getCLPostalCode($comuna, $address);
		return $call;
	}
}