<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCustomDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_custom_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('company_custom_doc_name', 255)->nullable();
            $table->string('company_doc_file_name', 100)->nullable();
            $table->string('random_number', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('company_id')->references('id')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('company_custom_documents');
        // DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
