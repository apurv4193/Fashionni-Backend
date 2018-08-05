<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderBoutiqueAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_boutique_attributes', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable();
            $table->integer('boutique_id')->nullable();
            $table->string('boutique_unique_code', 100)->nullable();

            $table->integer('confirmed_items')->nullable();
            $table->string('package_weight', 100)->nullable();

            $table->string('package_box_name', 100)->nullable();
            $table->string('package_size', 100)->nullable();
            $table->string('package_volumetric_weight', 100)->nullable();

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
        Schema::dropIfExists('order_boutique_attributes');
    }
}
