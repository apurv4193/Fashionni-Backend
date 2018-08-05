<?php

use Illuminate\Database\Seeder;
use App\Permissions;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement("SET FOREIGN_KEY_CHECKS=0");

        // \DB::table('users')->truncate();

        $permissions = [
            [
                'id' => 1,
                'company_id' => NULL,
                'slug' => 'boutique',
                'label_name' => 'Boutique',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 2,
                'company_id' => NULL,
                'slug' => 'boutique-reg',
                'label_name' => 'Boutique Registration',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 3,
                'company_id' => NULL,
                'slug' => 'boutique-bank',
                'label_name' => 'Boutique Bank',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 4,
                'company_id' => NULL,
                'slug' => 'boutique-tax',
                'label_name' => 'Boutique Tax',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 5,
                'company_id' => NULL,
                'slug' => 'boutique-customs',
                'label_name' => 'Boutique Customs',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 6,
                'company_id' => NULL,
                'slug' => 'boutique-user',
                'label_name' => 'Boutique User',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 7,
                'company_id' => NULL,
                'slug' => 'boutique-company',
                'label_name' => 'Boutique Company',
                'is_default' => 1,
                'default_edit_for' => '1,2,3',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 8,
                'company_id' => NULL,
                'slug' => 'boutique-product',
                'label_name' => 'Boutique Product',
                'is_default' => 1,
                'default_edit_for' => '1,2,3',
                'default_view_for' => '1,2,3',
                'created_at' => '2018-04-03 17:04:00',
                'updated_at' => '2018-04-03 17:04:00',
                'deleted_at' => NULL,
            ],
            [
                'id' => 9,
                'company_id' => NULL,
                'slug' => 'boutique-chat',
                'label_name' => 'Boutique Chat',
                'is_default' => 1,
                'default_edit_for' => '1,2',
                'default_view_for' => '1,2',
                'created_at' => '2018-05-05 00:01:00',
                'updated_at' => '2018-05-05 00:01:00',
                'deleted_at' => NULL,
            ]
        ];

        foreach($permissions as $key => $value)
        {
            Permissions::firstOrCreate([
                'id' => $value['id'],
                'company_id' => $value['company_id'],
                'slug' => $value['slug'],
                'label_name' => $value['label_name'],
                'is_default' => $value['is_default'],
                'default_edit_for' => $value['default_edit_for'],
                'default_view_for' => $value['default_view_for'],
                'created_at' => $value['created_at'],
                'updated_at' => $value['updated_at'],
                'deleted_at' => $value['deleted_at'],
            ]);
        }

        \DB::statement("SET FOREIGN_KEY_CHECKS=1");
    }
}
