<?php

namespace Database\Seeders;

use BranchSeeder;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optional test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed branches
        $this->call([
            BranchSeeder::class,
        ]);

         $this->call(PropertySeeder::class);
    }
}
