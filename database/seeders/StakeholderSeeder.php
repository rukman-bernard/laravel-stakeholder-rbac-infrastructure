<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
 use Spatie\Permission\Models\Role;

class StakeholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Enforce dependency:
         * Stakeholder seeding requires roles & permissions
         * from SpatieSeeder.
         *
         * Safe because SpatieSeeder should be idempotent.
         */
       

        if (Role::query()->count() === 0) {
            $this->command?->warn('Roles not found. Running SpatieSeeder...');
            $this->callOnce(SpatieSeeder::class);
        }


        $this->call([
            UserSeeder::class,
            StudentSeeder::class,
            // LecturerSeeder::class,
            EmployerSeeder::class,
        ]);
    }
}
