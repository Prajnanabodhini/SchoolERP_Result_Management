<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        Division::updateOrCreate(
            ['division_name' => 'A'],
            [
                'display_order' => 1,
                'is_active' => true
            ]
        );

        Division::updateOrCreate(
            ['division_name' => 'B'],
            [
                'display_order' => 2,
                'is_active' => true
            ]
        );
    }
}