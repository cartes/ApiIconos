<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

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
            'nombre' => 'Super Admin',
            'email' => 'email@email.com',
            'hash' => bcrypt('12345678'),
            'rol' => 'super-admin',
            'tenant_id' => '1',
        ]);

        setPermissionsTeamId('1');

        if (!$user->hasRole('Super-Admin')) {
            $user->assignRole($role);
        }

    }
}
