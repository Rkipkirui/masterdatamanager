<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            ['code' => '1', 'name' => 'KBT'],
            ['code' => '2', 'name' => 'IR'],
            ['code' => '3', 'name' => 'TRD'],
            ['code' => '4', 'name' => 'DST'],
            ['code' => '5', 'name' => 'DSN'],
            ['code' => '6', 'name' => 'GMN'],
            ['code' => '7', 'name' => 'KSM'],
            ['code' => '8', 'name' => 'MRF'],
            ['code' => '9', 'name' => 'MSA'],
            ['code' => '10', 'name' => 'NKR'],
            ['code' => '11', 'name' => 'VOI'],
            ['code' => '12', 'name' => 'MALINDI'],
            ['code' => '13', 'name' => 'NANYUKI'],
            ['code' => '14', 'name' => 'Dealers'],
            ['code' => '15', 'name' => 'THIKA'],
            ['code' => '16', 'name' => 'TOYOTA'],
        ];

        foreach ($properties as $property) {
            Property::updateOrCreate(
                ['code' => $property['code']],
                ['name' => $property['name']]
            );
        }
    }
}
