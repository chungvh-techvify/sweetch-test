<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('year_id');
            $table->unsignedInteger('age_id');
            $table->unsignedInteger('ethnicity_id');
            $table->unsignedInteger('gender_id');
            $table->unsignedInteger('area_id');
            $table->bigInteger('count');
            $table->timestamps();

            $table->foreign('year_id')->references('id')->on('years');
            $table->foreign('age_id')->references('id')->on('ages');
            $table->foreign('ethnicity_id')->references('id')->on('ethnicities');
            $table->foreign('gender_id')->references('id')->on('gender');
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
