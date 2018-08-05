<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventory', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('fashionni_id', 100)->nullable();
            $table->string('product_standard', 100)->nullable();
            $table->string('product_size', 10)->nullable();
            $table->tinyInteger('product_quantity')->unsigned()->comment('0:Sold out, 1:Not sold')->default(1);
            $table->string('product_warehouse', 100)->nullable();
            $table->tinyInteger('sold_by')->unsigned()->comment('0:Not sold, 1:Sold by superadmin, 2:Sold by admin' )->default(0);
            $table->string('product_inventory_unique_id', 100)->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('product_inventory');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
