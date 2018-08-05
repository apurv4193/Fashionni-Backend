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

$factory->define(App\Categories::class, function (Faker $faker) {
    
    $categoryData = App\Categories::get();
    $is_parent = 0;
    $categoryCount = $categoryData->count();
    if($categoryCount % 2 !== 0)
    {
        $lastCategory = App\Categories::orderBy('id','desc')->first();
        $is_parent = (isset($lastCategory) && !empty($lastCategory)) ? $lastCategory->id : 0;
    }
    return [
        'is_parent' => $is_parent,
        'category_name_en' => $faker->unique()->word,
        'category_name_ge' => NULL,
        'category_name_fr' => NULL,
        'category_name_it' => NULL,
        'category_name_sp' => NULL,
        'category_name_ru' => NULL,
        'category_name_jp' => NULL,
        'category_unique_id' => $faker->unique()->randomNumber(8),
        'category_level' => 0
    ];
});
