<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers_cart', function (Blueprint $table) {
            $table->string('customer_id',100)->nullable(false);
            $table->string('cart_token',100)->nullable()->default(NULL);
            $table->mediumText('cart_products')->nullable()->default(NULL);
            $table->primary('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('customers_cart');
    }
}
