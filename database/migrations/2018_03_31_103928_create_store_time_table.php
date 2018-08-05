<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_time', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->unsigned()->nullable();
            $table->string('mon_timing',100)->nullable();
            $table->string('tue_timing',100)->nullable();
            $table->string('wed_timing',100)->nullable();
            $table->string('thu_timing',100)->nullable();
            $table->string('fri_timing',100)->nullable();
            $table->string('sat_timing',100)->nullable();
            $table->string('sun_timing',100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('store_id')->references('id')->on('store');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('store_time');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
