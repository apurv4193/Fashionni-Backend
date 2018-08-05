<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyBankDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_bank_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('company_name', 100)->nullable();
            $table->longText('company_address')->nullable();
            $table->string('bank_name', 255)->nullable();
            $table->longText('bank_address')->nullable();
            $table->string('IBAN_account_no', 255)->nullable();
            $table->string('SWIFT_BIC', 100)->nullable();
            $table->string('bank_image', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('company');
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
        Schema::dropIfExists('company_bank_detail');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
