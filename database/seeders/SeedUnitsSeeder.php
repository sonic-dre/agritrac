<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class SeedUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogram',     'symbol' => 'kg',        'base_kg' => 1.0],
            ['name' => 'Metric Tonne', 'symbol' => 'MT',        'base_kg' => 1000.0],
            ['name' => '90kg Sack',    'symbol' => '90kg sack', 'base_kg' => 90.0],
            ['name' => '50kg Bag',     'symbol' => '50kg bag',  'base_kg' => 50.0],
            ['name' => 'Crate',        'symbol' => 'crate',     'base_kg' => null],
            ['name' => 'Litre',        'symbol' => 'L',         'base_kg' => null],
        ];
        foreach ($units as $u) {
            Unit::create($u);
        }
    }
}
