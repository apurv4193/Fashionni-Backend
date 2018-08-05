<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->integer('brand_id')->unsigned()->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->string('product_name_en', 255)->nullable();
            $table->string('product_name_ch', 255)->nullable();
            $table->string('product_name_ge', 255)->nullable();
            $table->string('product_name_fr', 255)->nullable();
            $table->string('product_name_it', 255)->nullable();
            $table->string('product_name_sp', 255)->nullable();
            $table->string('product_name_ru', 255)->nullable();
            $table->string('product_name_jp', 255)->nullable();
            $table->string('product_unique_id', 100)->unique()->nullable();
            $table->integer('category_level1_id')->unsigned()->nullable();
            $table->integer('category_level2_id')->unsigned()->nullable();
            $table->integer('category_level3_id')->unsigned()->nullable();
            $table->integer('category_level4_id')->unsigned()->nullable();
            $table->string('product_retail_price', 255)->nullable();
            $table->string('product_discount_rate', 255)->nullable();
            $table->string('product_discount_amount', 255)->nullable();
            $table->string('product_vat_rate', 255)->nullable();
            $table->string('product_vat', 255)->nullable();
            $table->string('product_outlet_price', 255)->nullable();
            $table->string('product_outlet_price_exclusive_vat', 255)->nullable();
            $table->string('fashionni_fees', 255)->nullable();
            $table->string('code_number', 255)->nullable();
            $table->string('code_image', 255)->nullable();
            $table->tinyInteger('is_published')->unsigned()->comment('0 - Draft, 1 - Published')->default(0);
            $table->tinyInteger('updated_by_boutique_admin')->unsigned()->comment('0 - Not Updated, 1 - Updated')->default(0);
            $table->string('brand_label_with_original_information_image', 255)->nullable();
            $table->string('wash_care_with_material_image', 255)->nullable();
            $table->string('short_description', 255)->nullable();
            $table->string('material_detail', 255)->nullable();
            $table->string('product_notice', 255)->nullable();
            $table->string('product_code_barcode', 255)->nullable();
            $table->string('product_code_boutique', 255)->nullable();
            $table->string('product_code_rfid', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('company');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('created_by')->references('id')->on('users');
            // $table->foreign('category_level1_id')->references('id')->on('categories');
            // $table->foreign('category_level2_id')->references('id')->on('categories');
            // $table->foreign('category_level3_id')->references('id')->on('categories');
            // $table->foreign('category_level4_id')->references('id')->on('categories');
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
        Schema::dropIfExists('products');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
