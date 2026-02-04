<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Transportas',
                'slug' => 'transportas',
                'icon' => 'truck',
                'is_active' => true,
                'position' => 1,
                'subcategories' => [
                    ['name' => 'Lengvieji automobiliai', 'slug' => 'lengvieji-automobiliai'],
                    ['name' => 'Motociklai', 'slug' => 'motociklai'],
                    ['name' => 'Sunkvežimiai', 'slug' => 'sunkvieziai'],
                    ['name' => 'Dalys ir priedai', 'slug' => 'dalys-ir-priedai'],
                ],
            ],
            [
                'name' => 'Nekilnojamas turtas',
                'slug' => 'nekilnojamas-turtas',
                'icon' => 'home',
                'is_active' => true,
                'position' => 2,
                'subcategories' => [
                    ['name' => 'Butai', 'slug' => 'butai'],
                    ['name' => 'Namai', 'slug' => 'namai'],
                    ['name' => 'Sklypai', 'slug' => 'sklypai'],
                    ['name' => 'Komercinės patalpos', 'slug' => 'komercines-patalpos'],
                ],
            ],
            [
                'name' => 'Paslaugos',
                'slug' => 'paslaugos',
                'icon' => 'briefcase',
                'is_active' => true,
                'position' => 3,
                'subcategories' => [
                    ['name' => 'Statybos ir remontas', 'slug' => 'statybos-remontas'],
                    ['name' => 'IT paslaugos', 'slug' => 'it-paslaugos'],
                    ['name' => 'Grožio paslaugos', 'slug' => 'grozio-paslaugos'],
                    ['name' => 'Transporto paslaugos', 'slug' => 'transporto-paslaugos'],
                ],
            ],
            [
                'name' => 'Prekės',
                'slug' => 'prekes',
                'icon' => 'shopping-bag',
                'is_active' => true,
                'position' => 4,
                'subcategories' => [
                    ['name' => 'Elektronika', 'slug' => 'elektronika'],
                    ['name' => 'Buitinė technika', 'slug' => 'buitine-technika'],
                    ['name' => 'Baldai', 'slug' => 'baldai'],
                    ['name' => 'Drabužiai ir avalynė', 'slug' => 'druziai-avalyne'],
                ],
            ],
            [
                'name' => 'Darbas',
                'slug' => 'darbas',
                'icon' => 'user-group',
                'is_active' => true,
                'position' => 5,
                'subcategories' => [
                    ['name' => 'IT srityje', 'slug' => 'it-srityje'],
                    ['name' => 'Statyba', 'slug' => 'statyba'],
                    ['name' => 'Prekyba', 'slug' => 'prekyba'],
                    ['name' => 'Kita', 'slug' => 'kita'],
                ],
            ],
            [
                'name' => 'Gyvūnai',
                'slug' => 'gyvunai',
                'icon' => 'heart',
                'is_active' => true,
                'position' => 6,
                'subcategories' => [
                    ['name' => 'Šunys', 'slug' => 'sunys'],
                    ['name' => 'Katės', 'slug' => 'kates'],
                    ['name' => 'Paukščiai', 'slug' => 'pauksciai'],
                    ['name' => 'Kiti gyvūnai', 'slug' => 'kiti-gyvunai'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'] ?? [];
            unset($categoryData['subcategories']);

            $category = \App\Models\Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );

            foreach ($subcategories as $position => $subData) {
                \App\Models\Subcategory::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => $subData['slug']
                    ],
                    [
                        'name' => $subData['name'],
                        'is_active' => true,
                        'position' => $position + 1,
                    ]
                );
            }
        }
    }
}
