<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colors', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('color_name_en', 255)->nullable();
            $table->string('color_name_ch', 255)->nullable();
            $table->string('color_name_ge', 255)->nullable();
            $table->string('color_name_fr', 255)->nullable();
            $table->string('color_name_it', 255)->nullable();
            $table->string('color_name_sp', 255)->nullable();
            $table->string('color_name_ru', 255)->nullable();
            $table->string('color_name_jp', 255)->nullable();
            $table->string('color_unique_id', 100)->unique()->nullable();
            $table->string('color_image', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('colors');
    }
}
