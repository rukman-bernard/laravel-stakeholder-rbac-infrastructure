<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{

    public function run(): void
{
    // Testing: only what you want, always ordered
    if (app()->environment('testing')) {
        $this->call([
            SpatieSeeder::class,
            StakeholderSeeder::class,
        ]);
        return;
    }

    $options = [
        'spatie' => 'SpatieSeeder (Roles & Permissions)',
        'stakeholder' => 'StakeholderSeeder (Users & Students) (auto-runs Spatie first)',
        'both' => 'Run Spatie + Stakeholder (recommended)',
        'none' => 'Exit without seeding',
    ];

    $choice = $this->command->choice(
        'Which seeder group would you like to run?',
        $options,
        'none'
    );

    match ($choice) {
        'spatie' => $this->call([SpatieSeeder::class]),
        'stakeholder' => $this->call([StakeholderSeeder::class]), // safe: enforces dependency internally
        'both' => $this->call([
            SpatieSeeder::class,
            StakeholderSeeder::class,
        ]),
        default => $this->command->info('No seeders were run.'),
    };
}


}
