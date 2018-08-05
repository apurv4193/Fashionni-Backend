<?php

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

//      \DB::table('users')->truncate();

        \DB::table('users')->insert(array(
            0 =>
            array(
                'id' => 1,
                'user_name' => 'Fashionni Admin',
                'name' => 'Fashionni Admin',
                'email' => 'fashionni@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-04-01 17:04:00',
                'updated_at' => '2018-04-01 17:04:00',
                'deleted_at' => NULL,
            ),
            1 =>
            array(
                'id' => 2,
                'user_name' => 'Fashionni Accouting',
                'name' => 'Fashionni Accouting',
                'email' => 'fashionni.accouting@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            ),
            2 =>
            array(
                'id' => 3,
                'user_name' => 'Fashionni Material',
                'name' => 'Fashionni Material',
                'email' => 'fashionni.material@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            ),
            3 =>
            array(
                'id' => 4,
                'user_name' => 'Fashionni Logistic',
                'name' => 'Fashionni Logistic',
                'email' => 'fashionni.logistic@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            ),
            4 =>
            array(
                'id' => 5,
                'user_name' => 'Fashionni Order',
                'name' => 'Fashionni Order',
                'email' => 'fashionni.order@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            ),
            5 =>
            array(
                'id' => 6,
                'user_name' => 'Fashionni Return',
                'name' => 'Fashionni Return',
                'email' => 'fashionni.return@inexture.in',
                'password' => bcrypt('12345678'),
                'position' => 'super admin',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            )
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }
}
