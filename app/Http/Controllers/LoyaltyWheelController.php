<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class LoyaltyWheelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return '<h2 style="width:100%;text-align:center">Ahora si Enrique<h2>';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return '<h2 style="width:100%;text-align:center">Ahora si Enrique??<h2>';
    }


    public function wheeling()
    {
        if (isset($_POST['email'])) {
            $rand_nmb = rand(1,100);
            $points = 0;
            $angle = 0;

            if ($rand_nmb <= 80) {
                $points = 200;
                $angle = 271;
            } else if ($rand_nmb <= 90) {
                $points = 500;
                $angle = 46;
            } else if ($rand_nmb <= 95) {
                $points = 1000;
                $angle = 226;
            } else if ($rand_nmb <= 98) {
                $points = 1500;
                $angle = 226;
            } else if ($rand_nmb <= 100) {
                $points = 2000;
                $angle = 316;
            }

            $useremail = 'pruebaregistro22@scalperscompany.com';
            $email_result = DB::table('loyalty_wheel')->select('*')->where('email', $useremail)->count();
            $check_status = DB::table('loyalty_wheel')->select('*')->where('email', $useremail)->count();
            $records = [
                'points' => $points,
                'email' => $useremail
            ];

            if ($check_status!=0) {
                http_response_code(422);
            } else {
                $insertRow = DB::table('loyalty_wheel')->insert($records);
            }

            if(isset($insertRow)){

                $response_array = json_encode(array('rand' => $rand_nmb, 'points' => $points, 'angle' => $angle + rand(0,43)));

				$lty_customer = Utilidades::getLoyaltyCustomer(
					$useremail,
					env('LTY_API_KEY'),
					env('LTY_API_PWD')
				); // Array

				if (isset($lty_customer["customers"][0])) {

					$merchant_id = (int)$lty_customer["customers"][0]["merchant_id"];

					Utilidades::giveLoyaltyPoints(
					$merchant_id,
					$points,
					"Bonus de bienvenida",
					env('LTY_API_KEY'),
					env('LTY_API_PWD'));

					return $response_array;
				} else {
					http_response_code(422);
				}

            }

            die();

            if (isset($insertRow) == true) {
                $response_array = array('rand' => $rand_nmb, 'points' => $points, 'angle' => $angle + rand(0,43));
                $get_ll_customer = callAPI('GET', "https://api.loyaltylion.com/v2/customers?email=".$useremail, false, 'loyalty_auth');
                $ll_response = json_decode($get_ll_customer);
                echo json_encode($ll_response);

                $get_ll_customer = callAPI('GET', "https://api.loyaltylion.com/v2/customers?email=".$useremail, false, 'loyalty_auth');
                $ll_response = json_decode($get_ll_customer);
                echo json_encode($ll_response);

                if (count($ll_response["customers"]) > 0) {
                    $merchant_id = (int)$ll_response["customers"][0]["merchant_id"];
                    $points = 1;
                    $reason = "Bonus ruleta bienvenida";
                    $ll_body_array = array('points' => $points, "reason" => $reason);
                    $ll_body_json = json_encode($ll_body_array);
                    $update_points = callAPI('POST', "https://api.loyaltylion.com/v2/customers/$merchant_id/points", $ll_body_json, 'loyalty_auth');
                }
            }

        } else {
            echo "Param missing: email not found";
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
