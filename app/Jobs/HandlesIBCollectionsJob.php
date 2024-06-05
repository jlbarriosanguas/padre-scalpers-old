<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Utilidades;


class HandlesIBCollectionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	
	private $storeCode;
	private $vendors;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($storeCode, $vendors)
    {
		$this->storeCode = $storeCode;
		$this->vendors = $vendors;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		Log::debug("ENTRA JOB");
		
        $store = Utilidades::shopifyStoreSel($this->storeCode);
		
		$query = '{
            collections (query:"tag:INVITED BRANDS") {
            edges {
              node {
                id
                title
                handle
              }
            }
          }
        }';

		$ibcollections = file_get_contents(Utilidades::shopifyBulkOperation($store, $query)["data"]["currentBulkOperation"]["url"]);
		$fileNameJsonl = 'cache_ibcollections_handles_bulk.jsonl';
		Storage::put(('/public/jsonl/'.$fileNameJsonl), '');
		Storage::put(('/public/jsonl/'.$fileNameJsonl), $ibcollections);
		$jsonl = Storage::get('/public/jsonl/'.$fileNameJsonl);
		
		
		$line = fopen(Storage::path("/public/jsonl/".$fileNameJsonl), "r");
		
		$collectionsHandles = [];
		do
		{
			$jsonl = fgets($line);
			if ($jsonl != '') {
				if (strpos($line["id"], "/Collection/") !== false) {
					$vendorCollection = str_replace("invited-brands-", "", $line['handle']);
					$vendorCollection = preg_replace('/\-[0-9]{4}/', "", $vendorCollection);
					
					if (in_array($vendorCollection, $vendors)) {
						array_push($collectionsHandles, $line['handle']);
					}
				}
			}
		} while(!feof($line));

		fclose($line);
		
		return $collectionsHandles;
    }
}
