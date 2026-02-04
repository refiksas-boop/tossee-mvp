<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Parduodu', 'slug' => 'parduodu'],
            ['name' => 'Perku', 'slug' => 'perku'],
            ['name' => 'Nuomoju', 'slug' => 'nuomoju'],
            ['name' => 'Ieškau', 'slug' => 'ieskau'],
            ['name' => 'Keičiu', 'slug' => 'keiciu'],
            ['name' => 'Dovanoju', 'slug' => 'dovanoju'],
        ];

        foreach ($types as $type) {
            \App\Models\ListingType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
