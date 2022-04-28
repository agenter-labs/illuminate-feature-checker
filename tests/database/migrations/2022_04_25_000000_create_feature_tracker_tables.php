<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureTrackerTables extends Migration
{

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('subscription', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('end_date');
            $table->unsignedTinyInteger('is_deleted')->default(0);
        });

		Schema::create('subscription_feature', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_id');
            $table->string('name', 40);
            $table->string('dtype', 10);
            $table->string('value', 40);
            $table->string('usage', 40);
        });
    }

    /**
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription');
        Schema::dropIfExists('subscription_feature');
    }
}