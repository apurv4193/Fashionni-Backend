<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminUsersTableSeeder::class);
    	$this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(UsersRolesTableSeeder::class);
        $this->call(ColorsTableSeeder::class);
        $this->call(BrandsTableSeeder::class);
        $this->call(MaterialsTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
    }

}
