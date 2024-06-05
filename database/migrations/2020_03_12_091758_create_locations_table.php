<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 5);
            $table->string('name');
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->decimal('longitude', 12, 9)->nullable();
            $table->decimal('latitude', 12, 9)->nullable();
            $table->string('city');
            $table->string('zip');
            $table->string('province');
            $table->string('province_code');
            $table->string('country');
            $table->string('country_name');
            $table->string('country_code');
            $table->string('phone');
            $table->boolean('legacy')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('locations');
    }
}
