<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsColorIdBrandIdMaterialIdToNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table)
        {
            $table->integer('color_id')->unsigned()->default(0)->after('category_id');
            $table->integer('brand_id')->unsigned()->default(0)->after('color_id');
            $table->integer('material_id')->unsigned()->default(0)->after('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('color_id');
            $table->dropColumn('brand_id');
            $table->dropColumn('material_id');
        });
    }
}
