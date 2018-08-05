<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->mediumText('company_ids')->nullable();
            $table->string('order_number', 40)->nullable();
            $table->dateTime('order_date')->nullable();
            $table->string('purchased_from', 100)->nullable();
            $table->string('payment_by', 100)->nullable();
            $table->tinyInteger('order_status')->unsigned()->default(1)->comment('1 - ordered, 2 - shipped, 3 - arrived, 4 - returned, 5 - closed');

            $table->text('billing_address')->nullable();
            $table->text('delivery_address')->nullable();

            $table->integer('boutique_count')->nullable();
            $table->integer('items_count')->nullable();

            $table->tinyInteger('shipping_type')->unsigned()->default(1)->comment('1 - Shippment Normal, 2 - Shippment Express');
            $table->string('shipping_price', 100)->nullable();
            $table->string('group_name', 100)->nullable();

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
        Schema::dropIfExists('orders');
    }
}
