<?php

/*   ___  _   ___  ___ ___    _   _ _____ ___ _    ___ ___   _   ___  ___ ___
 *  | _ \/_\ |   \| _ \ __|  | | | |_   _|_ _| |  |_ _|   \ /_\ |   \| __/ __|
 *  |  _/ _ \| |) |   / _|   | |_| | | |  | || |__ | || |) / _ \| |) | _|\__ \
 *  |_|/_/ \_\___/|_|_\___|   \___/  |_| |___|____|___|___/_/ \_\___/|___|___/  Test
 */

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class UtilidadesTest extends Controller
{
	/*   _  ___      ___   _______   _____
	 *  | |/ / |    /_\ \ / /_ _\ \ / / _ \
	 *  | ' <| |__ / _ \ V / | | \ V / (_) |
	 *  |_|\_\____/_/ \_\_/ |___| |_| \___/
     *
     *  klaviyoTrackAPI();
     *  klaviyoIdentifyAPI();
     *
     *  Klaviyo CRM profile properties:
     *
     *  $id
     *  $email
     *  $first_name
     *  $last_name
     *  $phone_numer
     *  $title
     *  $organization
     *  $city
     *  $region
     *  $country
     *  $zip
     *  $image
     *  $consent
	 */

    // WIP - Método para añadir métricas a los clientes mediante la Track API de Klaviyo.
    // Permite POST de eventos, al contrario que la MetricsAPI. Baja latencia.
	public static function klaviyoTrackAPITest($shopifyStoreSel, $event_name, $cust_properties, $properties)
	{

		$ly_client = new \GuzzleHttp\Client(['base_uri' => 'https://a.klaviyo.com/api/track']);

		$body = '{
            "token" : "' . $shopifyStoreSel["klyPublicKey"] . '",
			"event" : "' . $event_name . '",
			"customer_properties" : ' . $cust_properties . ',
			"properties" : ' . $properties . ',
			"time" : ' . date_timestamp_get(date_create()) .'
		}';

		$encoded_body = base64_encode($body);

        try
        {
            $request = $ly_client->get("?data=$encoded_body");
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $error = $e->getResponse()->getBody();
			exit($error);
		}

        // Output. Boolean.
		return json_decode($request->getBody(), true);
    }

    // WIP - Método para añadir propiedades a los clientes mediante la Track API de Klaviyo.
    // Similar a ProfileAPI, pero preparada para baja latencia. Usar con gran volúmen de operaciones.
    public static function klaviyoIdentifyAPITest($shopifyStoreSel, $properties)
    {
        $ly_client = new \GuzzleHttp\Client(['base_uri' => 'https://a.klaviyo.com/api/identify']);

        $body = '{
            "token" : "' . $shopifyStoreSel["klyPublicKey"] . '",
            "properties" : ' . $properties . '
        }';

        $encoded_body = base64_encode($body);

        try
        {
            $request = $ly_client->get("?data=$encoded_body");
        }
        catch (\GuzzleHttp\Exception\ClientException $e)
        {
            $error = $e->getResponse()->getBody();
            exit($error);
        }

        // Output. Boolean.
        return json_decode($request->getBody(), true);
    }

}
