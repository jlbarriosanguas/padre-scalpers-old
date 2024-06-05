<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoyaltyWheelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_wheel', function (Blueprint $table) {
			$table->string('email');
			$table->smallInteger('points');
			$table->boolean('roll_status');
			$table->timestamps();
			$table->primary('email');
            //$table->increments('id');
            //$table->string('name');
            //$table->string('email')->unique();
            //$table->string('password');
            //$table->rememberToken();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('loyalty_wheel');
    }
}
