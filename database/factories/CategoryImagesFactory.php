<?php

use Config as Config;
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

$factory->define(App\CategoryImages::class, function (Faker $faker) {
    return [
        'category_id' => $faker->randomDigitNotNull,
        'file_name' => $faker->image($dir = null, $width = 200, $height = 200, $category = 'fashion', $fullPath = false, $randomize = true, $word = null),
    ];
});
