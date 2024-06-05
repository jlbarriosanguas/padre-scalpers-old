<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\TestabUser;

class TestABController extends Controller
{
    function registerTaggedUser($email) {
		
		$existUser = TestabUser::where('email', $email)->first();
		if(empty($existUser)) {
			$newUser = new TestabUser();
			$newUser->email = $email;
			Log::info("el usuario " . $email . " es nuevo y se va a registrar en la base de datos");
			try {
				$newUser->save();
				Log::info($newUser->id);
				if(($newUser->id % 2) == 0) {
					$newUser->tag = "testab: A";
				} else {
					$newUser->tag = "testab: B";
				}
				$newUser->save();
				
				return $newUser->tag;
			} catch (Exception $e) {
				$message = $e->getMessage();
				log::error('Exception Message: '. $message);
				$code = $e->getCode();
				log::error('Exception Code: '. $code);
				$string = $e->__toString();
				log::error('Exception String: '. $string);
			}
		} else {
			Log::info("el usuario " . $email . " ya existe con el tag " . $existUser->tag);
			return $existUser->tag;
		}
    }
}
