<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedByBoutiqueSuperAdminToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table)
        {
            $table->tinyInteger('updated_by_boutique_super_admin')->unsigned()->default(0)->comment('0 - Not Updated, 1 - Updated')->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('updated_by_boutique_super_admin');
        });
    }
}
