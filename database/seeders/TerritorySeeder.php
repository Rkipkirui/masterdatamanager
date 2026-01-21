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
            ['code' => '1', 'name' => 'BUNGOMA'],
            ['code' => '2', 'name' => 'ELDORET'],
            ['code' => '3', 'name' => 'KISII'],
            ['code' => '4', 'name' => 'KISUMU TRADING'],
            ['code' => '5', 'name' => 'KITALE'],
            ['code' => '6', 'name' => 'KITENGELA'],
            ['code' => '7', 'name' => 'MALINDI'],
            ['code' => '8', 'name' => 'MOMBASA'],            
            ['code' => '9', 'name' => 'NAIROBI TRADING'],
            ['code' => '10', 'name' => 'NAKURU TRADING'],
            ['code' => '11', 'name' => 'THIKA'],
            ['code' => '12', 'name' => 'VOI'],
            ['code' => '13', 'name' => 'MRF'],
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
