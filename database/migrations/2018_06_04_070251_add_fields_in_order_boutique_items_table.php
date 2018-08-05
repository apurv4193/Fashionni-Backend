<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsInOrderBoutiqueItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_boutique_items', function (Blueprint $table)
        {
           $table->string('item_color', 100)->after('item_size')->nullable();
           $table->string('item_material', 100)->after('item_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_boutique_items', function (Blueprint $table) {
            $table->dropColumn('item_color');
            $table->dropColumn('item_material');
        });
    }
}
