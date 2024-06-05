<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ShopifyWebhookController extends Controller
{
    public function receiver(Request $request)
	{
		if (Utilidades::shopifyVerifyWebhook($request)) {

			// Incrementamos el contador por cada creación de carrito verificada
			$getValue = DB::table('shopify_cart')->where('id', 1)->value('int_data');
			$insertRow = DB::table('shopify_cart')->where('id', 1)->update(['int_data' => $getValue + 1]);

			$body = $request;
			// Añadimos en la DB el origen del último webhook
			// $insert_origin = DB::table('shopify_cart')->where('id', 1)->update(['request_data' =>$body]);

			if (isset($insertRow)) {

				//Recogemos de la DB el contador y el step
				$counter_value = DB::table('shopify_cart')->where('id', 1)->value('int_data');
				$step_value = DB::table('shopify_cart')->where('id', 2)->value('int_data');

				// Subimos el contador a todos los sites
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11303956283492.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11310302560317.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11589959057504.json', Utilidades::shopifyStoreSel('EU'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11313047699553.json', Utilidades::shopifyStoreSel('UK'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11345841619062.json', Utilidades::shopifyStoreSel('WW'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11443411189841.json', Utilidades::shopifyStoreSel('FR'), '{ "metafield": {"value": ' . $counter_value . ' } }');
				Utilidades::shopifyRESTAPI('PUT', 'metafields/11349425225814.json', Utilidades::shopifyStoreSel('PT'), '{ "metafield": {"value": ' . $counter_value . ' } }');

				// Actualizamos el step al superar los hitos (20k, 40k, 60k)
				if ($counter_value > 59999 && $step_value == 2) {
					$increment_step = DB::table('shopify_cart')->where('id', 2)->update(['int_data' => 3]);
					$step_value = DB::table('shopify_cart')->where('id', 2)->value('int_data');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353638174820.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11311805988925.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11659927158880.json', Utilidades::shopifyStoreSel('EU'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11342426275937.json', Utilidades::shopifyStoreSel('UK'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11363943809142.json', Utilidades::shopifyStoreSel('WW'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11447512727633.json', Utilidades::shopifyStoreSel('FR'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353101172822.json', Utilidades::shopifyStoreSel('PT'), '{ "metafield": {"value": ' . $step_value . ' } }');
				} else if  ($counter_value > 39999 && $step_value == 1) {
					$increment_step = DB::table('shopify_cart')->where('id', 2)->update(['int_data' => 2]);
					$step_value = DB::table('shopify_cart')->where('id', 2)->value('int_data');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353638174820.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11311805988925.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11659927158880.json', Utilidades::shopifyStoreSel('EU'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11342426275937.json', Utilidades::shopifyStoreSel('UK'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11363943809142.json', Utilidades::shopifyStoreSel('WW'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11447512727633.json', Utilidades::shopifyStoreSel('FR'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353101172822.json', Utilidades::shopifyStoreSel('PT'), '{ "metafield": {"value": ' . $step_value . ' } }');
				} else if  ($counter_value > 19999 && $step_value == 0) {
					$increment_step = DB::table('shopify_cart')->where('id', 2)->update(['int_data' => 1]);
					$step_value = DB::table('shopify_cart')->where('id', 2)->value('int_data');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353638174820.json', Utilidades::shopifyStoreSel('T1'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11311805988925.json', Utilidades::shopifyStoreSel('ES'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11659927158880.json', Utilidades::shopifyStoreSel('EU'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11342426275937.json', Utilidades::shopifyStoreSel('UK'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11363943809142.json', Utilidades::shopifyStoreSel('WW'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11447512727633.json', Utilidades::shopifyStoreSel('FR'), '{ "metafield": {"value": ' . $step_value . ' } }');
					Utilidades::shopifyRESTAPI('PUT', 'metafields/11353101172822.json', Utilidades::shopifyStoreSel('PT'), '{ "metafield": {"value": ' . $step_value . ' } }');
				}
			}
		}
	}
}
