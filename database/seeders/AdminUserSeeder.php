<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create user
        $admin = User::firstOrCreate(
            ['email' => 'ntegeoscar9@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // Change this password later
            ]
        );

        // Assign admin role
        $admin->assignRole($adminRole);
    }
}
