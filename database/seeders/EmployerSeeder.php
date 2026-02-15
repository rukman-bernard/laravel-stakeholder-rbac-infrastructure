<?php

namespace Database\Seeders;

use App\Models\Employer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployerSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1) Baseline employer (idempotent)
         * Keyed by email so re-running won't create duplicates.
         */
        Employer::updateOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name' => 'Employer',
                'image_path' => '',
                'password' => Hash::make('password'),
                'phone' => fake()->phoneNumber(),
            ]
        );

        /**
         * 2) Dummy employers (bounded growth)
         * We want 20 total employers (1 baseline + 19 dummy).
         * Only create the missing amount.
         */
        $targetTotal = 20;
        $current = Employer::count();

        if ($current < $targetTotal) {
            $toCreate = $targetTotal - $current;

            for ($i = 1; $i <= $toCreate; $i++) {
                $fakeCompany = fake()->company();

                // Keep your deterministic-ish email style, but ensure uniqueness
                $prefix = strtolower(strtok($fakeCompany, ' '));
                $prefix = preg_replace('/[^a-z0-9]/', '', $prefix) ?: 'company';

                // Make an email that is unlikely to collide with existing ones
                $email = $prefix . fake()->unique()->numberBetween(1000, 9999) . '@gmail.com';

                Employer::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fakeCompany,
                        'image_path' => '',
                        'password' => Hash::make('password'),
                        'phone' => fake()->phoneNumber(),
                    ]
                );
            }
        }

        /**
         * Note:
         * - We intentionally do NOT set remember_token here.
         *   Laravel will manage it during authentication.
         * - If you insist on having it seeded, set it only on create,
         *   but avoid regenerating it on every run.
         */
    }
}
