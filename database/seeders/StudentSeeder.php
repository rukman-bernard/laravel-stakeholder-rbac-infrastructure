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
            ['email' => 'student@gmail.com'], // unique identifier
            [
                'name' => 'student',
                'password' => Hash::make('password'),
                'phone' => '0712345678',
                'dob' => '2000-01-01',
            ]
        );

        /**
         * 2) Dummy students (bounded growth)
         * Only create more if total count is below a sensible threshold.
         * Adjust the threshold to match your project expectations.
         */
        $minTotal = 21; // 1 named + 20 factory
        $current = Student::count();

        if ($current < $minTotal) {
            Student::factory()->count($minTotal - $current)->create();
        }
    }
}
