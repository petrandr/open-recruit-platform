<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create a test user without syncing to search to avoid Scout errors
        User::withoutSyncingToSearch(function () {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        });
        // Seed job posting data
        $this->call(JobPostSeeder::class);
    }
}
