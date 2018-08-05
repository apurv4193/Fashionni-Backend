<?php

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

$factory->define(App\Colors::class, function (Faker $faker) {
   return [
        'color_name_en' => $faker->unique()->safeColorName,
        'color_name_ch' => NULL,
        'color_name_ge' => NULL,
        'color_name_fr' => NULL,       
        'color_name_it' => NULL,       
        'color_name_sp' => NULL,       
        'color_name_ru' => NULL,
        'color_name_jp' => NULL,        
        'color_unique_id' => $faker->unique()->randomNumber(8),
        'color_image' => NULL,
//      'color_image' => $faker->image($dir = null, $width = 200, $height = 200, $category = 'fashion', $fullPath = false, $randomize = true, $word = null),
    ];
});
