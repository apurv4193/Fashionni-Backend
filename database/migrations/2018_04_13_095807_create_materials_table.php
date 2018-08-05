<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('material_name_en', 255)->nullable();
            $table->string('material_name_ch', 255)->nullable();
            $table->string('material_name_ge', 255)->nullable();
            $table->string('material_name_fr', 255)->nullable();
            $table->string('material_name_it', 255)->nullable();
            $table->string('material_name_sp', 255)->nullable();
            $table->string('material_name_ru', 255)->nullable();
            $table->string('material_name_jp', 255)->nullable();
            $table->string('material_unique_id', 100)->unique()->nullable();
            $table->string('material_image', 255)->nullable();
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('materials');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
