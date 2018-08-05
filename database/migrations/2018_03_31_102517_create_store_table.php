<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('store_name', 100)->nullable();
            $table->string('store_slug', 100)->unique()->nullable();
            $table->string('store_image', 100)->nullable();
            $table->string('short_name', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->integer('postal_code')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('store_contact_person_name', 100)->nullable();
            $table->string('store_contact_person_email', 100)->nullable();
            $table->string('store_contact_person_telephone', 25)->nullable();
            $table->string('store_contact_person_position', 100)->nullable();
            $table->decimal('store_lat', 8, 2)->nullable();
            $table->decimal('store_lng', 8, 2)->nullable();
            $table->string('random_number', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('company');
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
        Schema::dropIfExists('store');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
