<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderBoutiqueItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_boutique_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable();
            $table->integer('boutique_id')->nullable();
            $table->string('boutique_unique_code', 100)->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_unique_code', 100)->nullable();

            $table->string('item_size', 100)->nullable();
            $table->string('item_warehouse_name', 100)->nullable();

            $table->enum('item_confirmed_status', ['0', '1'])->default('1')->comment('1 - In stock, 2 - Out of stock');
            $table->enum('item_shipped_status', ['0', '1'])->default('0')->comment('0 - No, 1 - Yes');
            $table->enum('item_returned_status', ['0', '1'])->default('0')->comment('0 - No, 1 - Yes');
            $table->enum('item_refunded_status', ['0', '1'])->default('0')->comment('0 - No, 1 - Yes');

            $table->string('delivery_country', 50)->nullable();
            $table->float('import_rate', 5, 2)->nullable();
            $table->double('prepaid_tax', 15, 2)->nullable();
            $table->double('item_wise_subtotal', 15, 2)->nullable();

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
        Schema::dropIfExists('order_boutique_items');
    }
}
