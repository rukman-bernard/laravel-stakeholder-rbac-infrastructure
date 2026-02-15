<?php

namespace Database\Seeders;

use App\Constants\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $namedUsers = [
            [
                'name' => 'System Admin',
                'email' => 'sysadmin@gmail.com',
                'role' => Roles::SYSTEM_ADMIN,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'role' => Roles::SUPER_ADMIN,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => Roles::ADMIN,
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => null,
            ],
        ];

        foreach ($namedUsers as $userData) {

            $role = $userData['role'];
            unset($userData['role']);

            /**
             * updateOrCreate makes this seeder idempotent:
             * - Creates user if not exists
             * - Updates name/password if exists
             */
            $user = User::updateOrCreate(
                ['email' => $userData['email']], // unique key
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'), // or fixed secure dev password
                ]
            );

            if ($role) {
                // Sync instead of assignRole to prevent duplicates
                $user->syncRoles([$role]);
            }
        }

        /**
         * Random users (only create if none exist)
         * Prevent infinite growth on repeated seeding
         */
        if (User::count() < 20) {
            User::factory(10)->create();
        }
    }
}
