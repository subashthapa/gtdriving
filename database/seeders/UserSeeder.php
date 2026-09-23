<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create roles if not already seeded
        $roles = ['SuperAdmin', 'Admin', 'Instructor', 'Learner'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create users and assign roles
        $users = [
            [
                'name' => 'Super Admin User',
                'phone' => '1234567890',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role' => 'SuperAdmin',
            ],
            [
                'name' => 'Admin User',
                'phone' => '2345678901',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Instructor User',
                'phone' => '3456789012',
                'email' => 'instructor@example.com',
                'password' => Hash::make('password'),
                'role' => 'Instructor',
            ],
            [
                'name' => 'Instructor Two',
                'phone' => '3456789013',
                'email' => 'instructor2@example.com',
                'password' => Hash::make('password'),
                'role' => 'Instructor',
            ],
            [
                'name' => 'Learner User',
                'phone' => '4567890123',
                'email' => 'learner@example.com',
                'password' => Hash::make('password'),
                'role' => 'Learner',
            ],
            [
                'name' => 'Learner Two',
                'phone' => '4567890124',
                'email' => 'learner2@example.com',
                'password' => Hash::make('password'),
                'role' => 'Learner',
            ],
            [
                'name' => 'Learner Three',
                'phone' => '4567890125',
                'email' => 'learner3@example.com',
                'password' => Hash::make('password'),
                'role' => 'Learner',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']], // Prevent duplicate users
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => $userData['password'],
                ]
            );

            $user->assignRole($userData['role']); // Assign role to the user
        }
    }
}
