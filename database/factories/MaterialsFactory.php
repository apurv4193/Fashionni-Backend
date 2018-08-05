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

$factory->define(App\Materials::class, function (Faker $faker) {
    return [
        'material_name_en' => $faker->unique()->word,
        'material_name_ch' => NULL,
        'material_name_ge' => NULL,
        'material_name_fr' => NULL,       
        'material_name_it' => NULL,       
        'material_name_sp' => NULL,       
        'material_name_ru' => NULL,
        'material_name_jp' => NULL,        
        'material_unique_id' => $faker->unique()->randomNumber(8),
        'material_image' => NULL,
//      'material_image' => $faker->image($dir = null, $width = 200, $height = 200, $category = 'fashion', $fullPath = false, $randomize = true, $word = null),
    ];
});
