<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Super-Admin',
            'tenant_id' => '1'
        ]);

        $user = User::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'email@email.com',
            'password' => bcrypt('12345678'),
            'tenant_id' => '1',
        ]);

        setPermissionsTeamId('1');

        if (!$user->hasRole('Super-Admin')) {
            $user->assignRole($role);
        }

    }
}
