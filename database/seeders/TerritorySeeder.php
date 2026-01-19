<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        $territories = [
            ['code' => '-1', 'name' => '-No Territory-'],
            ['code' => 'BUNGOMA', 'name' => 'BUNGOMA'],
            ['code' => 'ELDORET', 'name' => 'ELDORET'],
            ['code' => 'KISII', 'name' => 'KISII'],
            ['code' => 'KISUMU TRADING', 'name' => 'KISUMU TRADING'],
            ['code' => 'KITALE', 'name' => 'KITALE'],
            ['code' => 'KITENGELA', 'name' => 'KITENGELA'],
            ['code' => 'MALINDI', 'name' => 'MALINDI'],
            ['code' => 'MOMBASA', 'name' => 'MOMBASA'],
            ['code' => 'MRF', 'name' => 'MRF'],
            ['code' => 'NAIROBI TRADING', 'name' => 'NAIROBI TRADING'],
            ['code' => 'NAKURU TRADING', 'name' => 'NAKURU TRADING'],
            ['code' => 'THIKA', 'name' => 'THIKA'],
            ['code' => 'VOI', 'name' => 'VOI'],
        ];

        foreach ($territories as $territory) {
            DB::table('territories')->updateOrInsert(
                ['code' => $territory['code']],
                [
                    'name' => $territory['name'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
