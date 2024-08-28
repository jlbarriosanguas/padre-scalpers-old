<?php

namespace App\Http\Controllers;

use App\Jobs\HandlesIBCollectionsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Utilidades;
use Illuminate\Support\Carbon;
use Throwable;

class ShopifyHandlesController extends Controller
{
    public function getHandles($storeCode, $vendors)
    {
		$fileNameJsonl = 'cache_ibcollections_handles_bulk_' . $storeCode . '.jsonl';
        $storeCode = strtoupper($storeCode);
		$vendors = explode(",", $vendors);
		$store = Utilidades::shopifyStoreSel($storeCode);
		$now = Carbon::now()->timestamp;
		$fiveminutes = 300; // 5 minutes timestamp
		Log::debug(json_encode($vendors));
		
		$query = '{
            collections (query:"tag:INVITED BRANDS", sortKey:TITLE) {
            edges {
              node {
                id
                title
				handle
				productsCount
              }
            }
          }
        }';

		if (!Storage::exists('/public/jsonl/' . $fileNameJsonl)) {
			$ibcollections = file_get_contents(Utilidades::shopifyBulkOperation($store, $query)["data"]["currentBulkOperation"]["url"]);
			Storage::put(('/public/jsonl/'.$fileNameJsonl), '');
			Storage::put(('/public/jsonl/'.$fileNameJsonl), $ibcollections);
			$lastmod = filemtime(Storage::path('/public/jsonl/' . $fileNameJsonl));
			$fsize = filesize(Storage::path('/public/jsonl/' . $fileNameJsonl));
		} else {
			$lastmod = filemtime(Storage::path('/public/jsonl/' . $fileNameJsonl));
			$fsize = filesize(Storage::path('/public/jsonl/' . $fileNameJsonl));
			if (($lastmod + $fiveminutes) <= $now || $fsize == 0) {
				$ibcollections = file_get_contents(Utilidades::shopifyBulkOperation($store, $query)["data"]["currentBulkOperation"]["url"]);
				Storage::put(('/public/jsonl/'.$fileNameJsonl), '');
				Storage::put(('/public/jsonl/'.$fileNameJsonl), $ibcollections);
			}
			
		}
			
		
		$jsonl = Storage::get('/public/jsonl/'.$fileNameJsonl);
		
		
		$file = fopen(Storage::path("/public/jsonl/".$fileNameJsonl), "r");
		
		$collectionsHandles = [];
		do
		{
			$jsonl = fgets($file);
			if ($jsonl != '') {
				$line = json_decode($jsonl, true);
				// Log::debug("line->" . $line['handle']);
				
				if (strpos($line["id"], "/Collection/") !== false) {
					// Log::debug("handle->" . $line['handle']);
					if ($line['handle'] == "invited-brands-5351") {
						$vendorCollection = "invited-brands";
					} else {
						$vendorCollection = str_replace("invited-brands-", "", $line['handle']);
						$vendorCollection = preg_replace('/\-[0-9]{4}/', "", $vendorCollection);
					}
					// Log::debug("*****************************************************************");
					// Log::debug($vendorCollection);
					
					if ($vendorCollection == "mid-night-00-00") {
						$vendorCollection = "mid-night-00.00";
					}
					
					if (in_array($vendorCollection, $vendors)) {
						if ($line['productsCount'] > 0) {
							// Log::debug(preg_match('/-\s?[A-Za-z]{1}-/', $line['title']));
							if (preg_match('/-\s?[A-Za-z]{1}-/', $line['title']) == 1) {
								$originalVendor = str_replace("Invited Brands - ", "", $line['title']);
								$originalVendor = strtoupper(trim($originalVendor));
							} else {
								$originalVendor = explode("-", $line['title']);
								$originalVendor = strtoupper(trim(end($originalVendor)));
							}
							if ($originalVendor == "BECK SONDERGAARD") {
								$originalVendor = "BECKSONDERGAARD";
							}
							if ($line['title'] == "Invited Brands - Collection") {
								$originalVendor = "INVITED BRANDS";
							}
							array_push($collectionsHandles, [
								"handle" => $line['handle'],
								"productsCount" => $line['productsCount'],
								"vendor" => $originalVendor
							]);
						}
						// array_push($collectionsHandles[], $line['handle']);
						// array_push($collectionsHandles[$line['handle']], $line['productsCount']);
					}
				}
			}
		} while(!feof($file));
		
		fclose($file);

		// Log::debug($collectionsHandles);
		return $collectionsHandles;
	}
}
