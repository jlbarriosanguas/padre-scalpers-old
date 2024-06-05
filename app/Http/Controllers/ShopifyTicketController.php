<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Utilidades;

class ShopifyTicketController extends Controller
{
	public function index($origin, $id)
    {
        $order = Utilidades::shopifyRESTAPI('GET', 'orders/' . $id . '.json', Utilidades::shopifyStoreSel('T1'), '');
        return $order;
	}
}