<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreContactPersonImageToStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store', function(Blueprint $table) {
            $table->string('store_contact_person_image', 255)->nullable()->after('store_contact_person_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store', function($table) {
            $table->dropColumn('store_contact_person_image');
        });
    }
}
