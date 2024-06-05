<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\VerifyShopifyHmac;

class ShopifyHmacFilesController extends Controller
{
	public function getFile($filename)
	{
        return Storage::disk('private')->download('rrhh/applicants/'.$filename);
	}
}
