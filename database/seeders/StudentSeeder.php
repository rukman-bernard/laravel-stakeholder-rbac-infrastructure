<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Goals:
     * - Idempotent named student (safe to re-run)
     * - Dummy students do not grow infinitely on repeated seeding
     */
    public function run(): void
    {
        // 1) Named "baseline" student (idempotent)
        $student = Student::updateOrCreate(
            ['email' => 'student@example.com'], // unique identifier
            [
                'name' => 'student',
                'password' => Hash::make('password'),
                'phone' => '0712345678',
                'dob' => '2000-01-01',
            ]
        );
    }
}
