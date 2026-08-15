<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Standard;

class StandardSeeder extends Seeder
{
    public function run(): void
    {
        $standards = [
            'FIRST',
            'SECOND',
            'THIRD',
            'FOURTH',
            'FIFTH',
            'SIXTH',
            'SEVENTH',
            'EIGHTH',
            'NINTH',
            'TENTH'
        ];

        foreach ($standards as $key => $name) {

            Standard::updateOrCreate(
                ['standard_name' => $name],
                [
                    'display_order' => $key + 1,
                    'is_active' => 1
                ]
            );

        }
    }
}