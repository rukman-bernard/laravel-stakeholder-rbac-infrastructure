<?php

namespace Database\Seeders;

use App\Models\Employer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployerSeeder extends Seeder
{
    public function run(): void
    {
        Employer::updateOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name' => 'Employer',
                'image_path' => '',
                'password' => Hash::make('password'),
                'phone' => fake()->phoneNumber(),
            ]
        );

    }
}
