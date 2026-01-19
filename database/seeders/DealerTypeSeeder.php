<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DealerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => '2W', 'name' => '2W'],
            ['code' => '3W', 'name' => '3W'],
        ];

        foreach ($types as $type) {
            DB::table('dealer_types')->updateOrInsert(
                ['code' => $type['code']],
                ['name' => $type['name'], 'updated_at' => now()]
            );
        }
    }
}

