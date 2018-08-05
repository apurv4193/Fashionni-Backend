<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderBoutiqueDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_boutique_documents', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable();
            $table->integer('boutique_id')->nullable();
            $table->string('boutique_unique_code', 100)->nullable();

            $table->string('invoice_doc', 100)->nullable();
            $table->string('parcel_doc', 100)->nullable();
            $table->string('export_doc', 100)->nullable();
            $table->string('others_doc', 100)->nullable();

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
        Schema::dropIfExists('order_boutique_documents');
    }
}
