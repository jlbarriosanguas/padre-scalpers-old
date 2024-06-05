<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropStausClomunRoyaltyWheel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loyalty_wheel', function (Blueprint $table) {
            $table->dropColumn('roll_status');
            $table->dropColumn('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('loyalty_wheel', function (Blueprint $table) {
            // $table->tinyInteger('roll_status');
            // $table->dateTime('updated_at');
        // });
    }
}
