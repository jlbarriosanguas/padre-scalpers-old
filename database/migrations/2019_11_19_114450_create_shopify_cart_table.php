<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_cart', function (Blueprint $table) {
            $table->increments('id');
            $table->text('description');
            $table->string('origin_store', 100);
			$table->text('request_data');
			$table->integer('int_data');
        });
		
		DB::table('shopify_cart')->insert([
            'description' => 'Init',
            'origin_store' => '',
			'request_data' => '',
			'int_data' => 7777
		]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('shopify_cart');
    }
}