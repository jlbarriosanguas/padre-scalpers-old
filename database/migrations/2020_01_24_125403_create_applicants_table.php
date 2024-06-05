<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('job_id')->unsigned();
            $table->boolean('saved')->default(false);
            $table->string('name');
            $table->string('surname');
            $table->date('birthday');
            $table->string('phone');
            $table->string('email');
            $table->string('studies');
            $table->string('english_level');
            $table->string('retail_exp');
            $table->string('location')->nullable();
            $table->string('job');
            $table->string('last_exp');
            $table->string('country');
            $table->string('city');
            $table->string('time_availability');
            $table->string('travel_availability');
            $table->string('curriculum')->default('No');
            $table->string('photo')->default('No');
            $table->string('motivation_letter')->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('applicants');
    }
}
