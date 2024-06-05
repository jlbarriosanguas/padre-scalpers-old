<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use Illuminate\Support\Facades\Log;


class ShopifyCarrouselController extends Controller
{
	public function completaLook($id, $storeCode) {
	
		Log::debug("completaLook");
		Log::debug($id);
		Log::debug($storeCode);
		
		$tags = Utilidades::retrieveShopifyProductsCompletLook($id, $storeCode);
		
		Log::debug(json_encode($tags, true));
		
		return response("ok", 200);
	}
	
	public function relatedProducts($id, $storeCode) {
		
		Log::debug("relatedProducts");
		Log::debug($id);
		Log::debug($storeCode);
		
		$tags = Utilidades::retrieveShopifyProductsCompletLook($id, $storeCode);
		
		Log::debug(json_encode($tags, true));
		
		
		return response("ok", 200);
	}
}
