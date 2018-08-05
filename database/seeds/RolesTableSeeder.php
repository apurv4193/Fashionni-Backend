<?php

use Illuminate\Database\Seeder;
use App\Roles;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'sales' => 'Sales',
            'custom' => 'Custom',
        ];

        foreach($roles as $slug => $role) {
            Roles::firstOrCreate(['slug' => $slug, 'name' => $role]);
        }
    }
}