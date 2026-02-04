<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lithuania = \App\Models\Location::updateOrCreate(
            ['slug' => 'lietuva'],
            [
                'name' => 'Lietuva',
                'type' => 'country',
                'parent_id' => null,
            ]
        );

        $cities = [
            'Vilnius',
            'Kaunas',
            'Klaipėda',
            'Šiauliai',
            'Panevėžys',
            'Alytus',
            'Marijampolė',
            'Mažeikiai',
            'Jonava',
            'Utena',
            'Kėdainiai',
            'Telšiai',
            'Tauragė',
            'Ukmergė',
            'Visaginas',
            'Plungė',
            'Palanga',
            'Kretinga',
            'Šilutė',
            'Radviliškis',
        ];

        foreach ($cities as $city) {
            \App\Models\Location::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($city)],
                [
                    'name' => $city,
                    'type' => 'city',
                    'parent_id' => $lithuania->id,
                ]
            );
        }
    }
}
