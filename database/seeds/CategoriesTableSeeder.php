<?php

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      factory(App\Categories::class, 10)->create()->each(function ($b) {
            $b->category_images()->save(factory(App\CategoryImages::class)->make());
        });
    }
}
