<?php

use Illuminate\Database\Seeder;

class UsersRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        // \DB::table('users')->truncate();

        \DB::table('user_roles')->insert(array(
            0 =>
            array(
                'id' => 1,
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => '2018-04-01 17:04:00',
                'updated_at' => '2018-04-01 17:04:00'
            ),
            1 =>
            array(
                'id' => 2,
                'user_id' => 2,
                'role_id' => 1,
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00'
            ),
            2 =>
            array(
                'id' => 3,
                'user_id' => 3,
                'role_id' => 1,
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00'
            ),
            3 =>
            array(
                'id' => 4,
                'user_id' => 4,
                'role_id' => 1,
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00'
            ),
            4 =>
            array(
                'id' => 5,
                'user_id' => 5,
                'role_id' => 1,
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00'
            ),
            5 =>
            array(
                'id' => 6,
                'user_id' => 6,
                'role_id' => 1,
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00'
            )
        ));

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }
}
