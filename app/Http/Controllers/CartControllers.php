<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;
use App\Jobs\CreateCart;

class CartControllers
{
    public function checkCartToken(Request $request){
        $customerID = $request->input('customerID');
        $cart = $request->input('cart');

        $customerData = DB::table('customers_cart')->select('cart_token')->where('customer_id',$customerID)->get();

        if(count($customerData) == 0){
            $this::createCartToken($customerID,$cart);

        } elseif ($customerData[0]->cart_token == null){
            DB::table('customers_cart')
            ->where('customer_id',$customerID)
            ->update([
                'cart_token' => $cart,
            ]);
        } else {
            $cart = $customerData[0]->cart_token;
        }

        return $cart;

    }

    public static function createCartToken($customerID,$cart){
        CreateCart::dispatch($customerID,$cart);
    }

    public static function modifyCartToken(Request $request){
        $customerID = $request->input('customerID');
        $cart = $request->input('cart');

        DB::table('customers_cart')
            ->where('customer_id',$customerID)
            ->update([
                'cart_token' => $cart,
            ]);
    }

    public function removeCartToken(Request $request){
        $customerID = $request->input('customerID');

        DB::table('customers_cart')
            ->where('customer_id',$customerID)
            ->update([
                'cart_token' => null,
            ]);

        return 'Cart removed';
    }
}