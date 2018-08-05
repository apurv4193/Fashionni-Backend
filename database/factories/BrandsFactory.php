<?php

use Helpers as Helpers;
use Faker\Generator as Faker;
use Faker\Factory as Factory;


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Brands::class, function (Faker $faker) {
    $brand_name = $faker->unique()->company;
    $brand_slug = Helpers::createSlug($brand_name);
    $getBrands = App\Brands::where('brand_slug', $brand_slug)->first();
    if(!empty($getBrands))
    {
        $brand_slug = $brand_slug.'_'.mt_rand();
    }
    return [
        'brand_name' => $brand_name,
        'brand_slug' => $brand_slug,
        'brand_image' => NULL,
//      'brand_image' => $faker->image($dir = null, $width = 200, $height = 200, $category = 'fashion', $fullPath = false, $randomize = true, $word = null),
    ];
});
