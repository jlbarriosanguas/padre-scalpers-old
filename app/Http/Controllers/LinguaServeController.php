<?php

/**
 * ShopifyController. PSR-12 compliant. Made with ❤ by the Scalpers eCommerce team.
 */

namespace App\Http\Controllers;

use App\Jobs\RegisterUsersJob;
use App\Services\ShopifyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LinguaServeController extends Controller
{
    public function __construct(
        protected ShopifyService $shopifyService
    ) {
        // Empty
    }

    public function changeDate(Request $request): Response
    {
        Log::debug("changeDAte");
        // Log::debug($request->toarray());
        $body = json_decode($request->getContent());
        Log::debug($body);

        $response = response('ok', 200);
        return $response;
    }
}
