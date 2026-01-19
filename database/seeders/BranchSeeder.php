<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['code' => 'TRD', 'name' => 'Nairobi'],
            ['code' => 'KIT', 'name' => 'Kitengela'],
            ['code' => 'KTL', 'name' => 'Kitale'],
            ['code' => 'THK', 'name' => 'Thika'],
            ['code' => 'MSA', 'name' => 'Mombasa'],
            ['code' => 'MAL', 'name' => 'Malindi'],
            ['code' => 'VOI', 'name' => 'Voi'],
            ['code' => 'BUG', 'name' => 'Bungoma'],
            ['code' => 'ELD', 'name' => 'Eldoret'],
            ['code' => 'NKR', 'name' => 'Nakuru'],
            ['code' => 'KSI', 'name' => 'Kisii'],
            ['code' => 'KSM', 'name' => 'Kisumu'],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                ['name' => $branch['name']]
            );
        }
    }
}
