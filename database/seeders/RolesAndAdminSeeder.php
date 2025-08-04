<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'storekeeper']);
        Role::firstOrCreate(['name' => 'operator']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'ntegeoscar9@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'), // Set your own secure password
            ]
        );

        // Assign admin role
        $admin->assignRole($adminRole);
    }
}
