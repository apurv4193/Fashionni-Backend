<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('category_name_en', 255)->nullable();
            $table->string('category_name_ch', 255)->nullable();
            $table->string('category_name_ge', 255)->nullable();
            $table->string('category_name_fr', 255)->nullable();
            $table->string('category_name_it', 255)->nullable();
            $table->string('category_name_sp', 255)->nullable();
            $table->string('category_name_ru', 255)->nullable();
            $table->string('category_name_jp', 255)->nullable();
            $table->string('category_unique_id', 100)->unique()->nullable();
            $table->tinyInteger('category_level')->unsigned()->comment('1:Category1, 2:Category2, 3:Category3, 4:Category4')->default(0);
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
        Schema::dropIfExists('categories');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
