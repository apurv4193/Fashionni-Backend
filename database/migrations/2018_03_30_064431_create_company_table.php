<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->increments('id');
            $table->string('register_number', 100)->nullable();
            $table->date('register_date')->nullable();
            $table->string('company_name', 100)->nullable();
            $table->string('court_name', 100)->nullable();
            $table->string('legal_person', 100)->nullable();
            $table->string('general_manager', 100)->nullable();
            $table->string('company_image', 255)->nullable();
            $table->longText('address')->nullable();
            $table->integer('postal_code')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('company_email', 100)->unique()->nullable();
            $table->string('website', 255)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('whatsapp', 25)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('wechat', 25)->nullable();
            $table->string('pinterest', 255)->nullable();
            $table->string('contact_person_first_name', 100)->nullable();
            $table->string('contact_person_last_name', 100)->nullable();
            $table->enum('contact_person_gender', array('male', 'female','other'))->default('male');
            $table->string('contact_person_position', 100)->nullable();
            $table->string('contact_person_telefon', 25)->nullable();
            $table->string('contact_person_fax', 50)->nullable();
            $table->string('contact_person_mo_no', 25)->nullable();
            $table->string('contact_person_email', 100)->nullable();
            $table->string('tax_company_name', 100)->nullable();
            $table->string('EUTIN', 100)->comment('EU Tax Identification Number (TIN)')->nullable();
            $table->string('NTIN', 100)->comment('National Tax Identification Number')->nullable();
            $table->string('LTA', 255)->comment('Local Tax Authorities')->nullable();
            $table->string('main_custom_office', 255)->nullable();
            $table->string('EORI', 255)->comment('EU Economics Operators Registration and Identification Number')->nullable();
            $table->string('country_code', 50)->nullable();
            $table->string('custom_company_name', 100)->nullable();
            $table->string('custom_country', 100)->nullable();
            $table->enum('status', array('active', 'inactive'))->default('active');
            $table->string('company_slug', 100)->unique()->nullable();
            $table->string('random_number', 255)->nullable();
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('company');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
