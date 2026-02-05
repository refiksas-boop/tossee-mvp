<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeOption;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Get categories
        $transportas = Category::where('slug', 'transportas')->first();
        $nekilnojamas = Category::where('slug', 'nekilnojamas-turtas')->first();
        $paslaugos = Category::where('slug', 'paslaugos')->first();
        $prekes = Category::where('slug', 'prekes')->first();
        $darbas = Category::where('slug', 'darbas')->first();
        $gyvunai = Category::where('slug', 'gyvunai')->first();

        // ==================== TRANSPORTAS ====================
        if ($transportas) {
            $this->createTransportAttributes($transportas->id);
        }

        // ==================== NEKILNOJAMAS TURTAS ====================
        if ($nekilnojamas) {
            $this->createRealEstateAttributes($nekilnojamas->id);
        }

        // ==================== PASLAUGOS ====================
        if ($paslaugos) {
            $this->createServiceAttributes($paslaugos->id);
        }

        // ==================== PREKĖS ====================
        if ($prekes) {
            $this->createGoodsAttributes($prekes->id);
        }

        // ==================== DARBAS ====================
        if ($darbas) {
            $this->createJobAttributes($darbas->id);
        }

        // ==================== GYVŪNAI ====================
        if ($gyvunai) {
            $this->createPetAttributes($gyvunai->id);
        }
    }

    private function createTransportAttributes($categoryId)
    {
        // Markė
        $marke = Attribute::updateOrCreate(
            ['slug' => 'marke', 'category_id' => $categoryId],
            [
                'name' => 'Markė',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 1,
            ]
        );
        $this->createOptions($marke->id, ['Toyota', 'Volkswagen', 'BMW', 'Mercedes-Benz', 'Audi', 'Ford', 'Opel', 'Renault', 'Peugeot', 'Volvo', 'Honda', 'Mazda', 'Nissan', 'Škoda', 'Kia', 'Hyundai', 'Kita']);

        // Modelis
        Attribute::updateOrCreate(
            ['slug' => 'modelis', 'category_id' => $categoryId],
            [
                'name' => 'Modelis',
                'type' => 'text',
                'is_required' => true,
                'filterable' => false,
                'position' => 2,
            ]
        );

        // Pagaminimo metai
        Attribute::updateOrCreate(
            ['slug' => 'metai', 'category_id' => $categoryId],
            [
                'name' => 'Pagaminimo metai',
                'type' => 'number',
                'is_required' => true,
                'filterable' => true,
                'position' => 3,
            ]
        );

        // Rida (km)
        Attribute::updateOrCreate(
            ['slug' => 'rida', 'category_id' => $categoryId],
            [
                'name' => 'Rida (km)',
                'type' => 'number',
                'is_required' => true,
                'filterable' => true,
                'position' => 4,
            ]
        );

        // Kuro tipas
        $kuras = Attribute::updateOrCreate(
            ['slug' => 'kuro-tipas', 'category_id' => $categoryId],
            [
                'name' => 'Kuro tipas',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 5,
            ]
        );
        $this->createOptions($kuras->id, ['Benzinas', 'Dyzelinas', 'Elektra', 'Hibridas', 'Dujos (LPG)', 'Dujos (CNG)', 'Kita']);

        // Variklio tūris (l)
        Attribute::updateOrCreate(
            ['slug' => 'variklio-turis', 'category_id' => $categoryId],
            [
                'name' => 'Variklio tūris (l)',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 6,
            ]
        );

        // Galia (kW)
        Attribute::updateOrCreate(
            ['slug' => 'galia-kw', 'category_id' => $categoryId],
            [
                'name' => 'Galia (kW)',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 7,
            ]
        );

        // Pavarų dėžė
        $pavaru_deze = Attribute::updateOrCreate(
            ['slug' => 'pavaru-deze', 'category_id' => $categoryId],
            [
                'name' => 'Pavarų dėžė',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 8,
            ]
        );
        $this->createOptions($pavaru_deze->id, ['Mechaninė', 'Automatinė', 'Pusiau automatinė', 'Variatorius']);

        // Kėbulo tipas
        $kebulas = Attribute::updateOrCreate(
            ['slug' => 'kebulo-tipas', 'category_id' => $categoryId],
            [
                'name' => 'Kėbulo tipas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 9,
            ]
        );
        $this->createOptions($kebulas->id, ['Sedanas', 'Universalas', 'Hečbekas', 'Visureigis/SUV', 'Kabrioletas', 'Kupė', 'Mikroautobusas', 'Pikapaas', 'Kita']);

        // Spalva
        $spalva = Attribute::updateOrCreate(
            ['slug' => 'spalva', 'category_id' => $categoryId],
            [
                'name' => 'Spalva',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 10,
            ]
        );
        $this->createOptions($spalva->id, ['Juoda', 'Balta', 'Pilka', 'Sidabrinė', 'Mėlyna', 'Raudona', 'Žalia', 'Geltona', 'Oranžinė', 'Ruda', 'Kita']);

        // Vairas
        $vairas = Attribute::updateOrCreate(
            ['slug' => 'vairas', 'category_id' => $categoryId],
            [
                'name' => 'Vairo padėtis',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 11,
            ]
        );
        $this->createOptions($vairas->id, ['Kairėje', 'Dešinėje']);

        // VIN kodas
        Attribute::updateOrCreate(
            ['slug' => 'vin-kodas', 'category_id' => $categoryId],
            [
                'name' => 'VIN kodas',
                'type' => 'text',
                'is_required' => false,
                'filterable' => false,
                'position' => 12,
            ]
        );
    }

    private function createRealEstateAttributes($categoryId)
    {
        // Plotas (m²)
        Attribute::updateOrCreate(
            ['slug' => 'plotas', 'category_id' => $categoryId],
            [
                'name' => 'Plotas (m²)',
                'type' => 'number',
                'is_required' => true,
                'filterable' => true,
                'position' => 1,
            ]
        );

        // Kambarių skaičius
        $kambariai = Attribute::updateOrCreate(
            ['slug' => 'kambariu-skaicius', 'category_id' => $categoryId],
            [
                'name' => 'Kambarių skaičius',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 2,
            ]
        );
        $this->createOptions($kambariai->id, ['1', '2', '3', '4', '5', '6+']);

        // Aukštas
        Attribute::updateOrCreate(
            ['slug' => 'aukstas', 'category_id' => $categoryId],
            [
                'name' => 'Aukštas',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 3,
            ]
        );

        // Aukštų skaičius
        Attribute::updateOrCreate(
            ['slug' => 'aukstu-skaicius', 'category_id' => $categoryId],
            [
                'name' => 'Aukštų skaičius pastate',
                'type' => 'number',
                'is_required' => false,
                'filterable' => false,
                'position' => 4,
            ]
        );

        // Pastato tipas
        $pastato_tipas = Attribute::updateOrCreate(
            ['slug' => 'pastato-tipas', 'category_id' => $categoryId],
            [
                'name' => 'Pastato tipas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 5,
            ]
        );
        $this->createOptions($pastato_tipas->id, ['Mūrinis', 'Blokinis', 'Monolitinis', 'Medinis', 'Rąstinis', 'Karkasinis', 'Kita']);

        // Stato metai
        Attribute::updateOrCreate(
            ['slug' => 'stato-metai', 'category_id' => $categoryId],
            [
                'name' => 'Statybos metai',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 6,
            ]
        );

        // Šildymas
        $sildymas = Attribute::updateOrCreate(
            ['slug' => 'sildymas', 'category_id' => $categoryId],
            [
                'name' => 'Šildymas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 7,
            ]
        );
        $this->createOptions($sildymas->id, ['Centrinis', 'Dujinis', 'Elektrinis', 'Kietu kuru', 'Geoterminis', 'Kita', 'Nėra']);

        // Įrengimas
        $irengimas = Attribute::updateOrCreate(
            ['slug' => 'irengimas', 'category_id' => $categoryId],
            [
                'name' => 'Įrengimas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 8,
            ]
        );
        $this->createOptions($irengimas->id, ['Įrengtas', 'Dalinai įrengtas', 'Neįrengtas', 'Reikalingas remontas']);

        // Ypatybės (multiselect)
        $ypatybes = Attribute::updateOrCreate(
            ['slug' => 'ypatybes', 'category_id' => $categoryId],
            [
                'name' => 'Ypatybės',
                'type' => 'multiselect',
                'is_required' => false,
                'filterable' => true,
                'position' => 9,
            ]
        );
        $this->createOptions($ypatybes->id, ['Balkonas', 'Terasa', 'Rūsys', 'Garažas', 'Liftas', 'Apsauga', 'Internetas', 'Oro kondicionavimas']);
    }

    private function createServiceAttributes($categoryId)
    {
        // Paslaugos trukmė
        Attribute::updateOrCreate(
            ['slug' => 'trukme', 'category_id' => $categoryId],
            [
                'name' => 'Trukmė',
                'type' => 'text',
                'is_required' => false,
                'filterable' => false,
                'position' => 1,
            ]
        );

        // Darbo vieta
        $darbo_vieta = Attribute::updateOrCreate(
            ['slug' => 'darbo-vieta', 'category_id' => $categoryId],
            [
                'name' => 'Darbo vieta',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 2,
            ]
        );
        $this->createOptions($darbo_vieta->id, ['Galiu atvykti', 'Tik pas save', 'Nuotoliniu būdu', 'Abiem būdais']);

        // Patirtis (metai)
        Attribute::updateOrCreate(
            ['slug' => 'patirtis-metai', 'category_id' => $categoryId],
            [
                'name' => 'Patirtis (metai)',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 3,
            ]
        );
    }

    private function createGoodsAttributes($categoryId)
    {
        // Būklė
        $bukle = Attribute::updateOrCreate(
            ['slug' => 'bukle', 'category_id' => $categoryId],
            [
                'name' => 'Būklė',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 1,
            ]
        );
        $this->createOptions($bukle->id, ['Nauja', 'Naudota - labai gera', 'Naudota - gera', 'Naudota - patenkinama', 'Sugadinta']);

        // Gamintojas
        Attribute::updateOrCreate(
            ['slug' => 'gamintojas', 'category_id' => $categoryId],
            [
                'name' => 'Gamintojas',
                'type' => 'text',
                'is_required' => false,
                'filterable' => true,
                'position' => 2,
            ]
        );

        // Modelis
        Attribute::updateOrCreate(
            ['slug' => 'modelis-preke', 'category_id' => $categoryId],
            [
                'name' => 'Modelis',
                'type' => 'text',
                'is_required' => false,
                'filterable' => false,
                'position' => 3,
            ]
        );

        // Garantija
        $garantija = Attribute::updateOrCreate(
            ['slug' => 'garantija', 'category_id' => $categoryId],
            [
                'name' => 'Garantija',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 4,
            ]
        );
        $this->createOptions($garantija->id, ['Yra', 'Nėra', 'Pasibaigusi']);
    }

    private function createJobAttributes($categoryId)
    {
        // Darbo tipas
        $darbo_tipas = Attribute::updateOrCreate(
            ['slug' => 'darbo-tipas', 'category_id' => $categoryId],
            [
                'name' => 'Darbo tipas',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 1,
            ]
        );
        $this->createOptions($darbo_tipas->id, ['Visą darbo dieną', 'Ne visą darbo dieną', 'Projektinis', 'Sezoninis', 'Praktika/Stažuotė', 'Savanorystė']);

        // Darbo grafikas
        $grafikas = Attribute::updateOrCreate(
            ['slug' => 'darbo-grafikas', 'category_id' => $categoryId],
            [
                'name' => 'Darbo grafikas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 2,
            ]
        );
        $this->createOptions($grafikas->id, ['Lankstus', 'Nuotolinis', 'Hibridinis', 'Biure', 'Pamaininis']);

        // Reikalinga patirtis
        $patirtis = Attribute::updateOrCreate(
            ['slug' => 'reikalinga-patirtis', 'category_id' => $categoryId],
            [
                'name' => 'Reikalinga patirtis',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 3,
            ]
        );
        $this->createOptions($patirtis->id, ['Be patirties', '< 1 metų', '1-3 metai', '3-5 metai', '5-10 metų', '> 10 metų']);

        // Išsilavinimas
        $issilavinimas = Attribute::updateOrCreate(
            ['slug' => 'issilavinimas', 'category_id' => $categoryId],
            [
                'name' => 'Išsilavinimas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 4,
            ]
        );
        $this->createOptions($issilavinimas->id, ['Pradinis', 'Pagrindinis', 'Vidurinis', 'Profesinis', 'Aukštesnysis', 'Aukštasis universitetinis', 'Aukštasis neuniversitetinis']);

        // Atlyginimas (nuo)
        Attribute::updateOrCreate(
            ['slug' => 'atlyginimas-nuo', 'category_id' => $categoryId],
            [
                'name' => 'Atlyginimas nuo (€)',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 5,
            ]
        );

        // Atlyginimas (iki)
        Attribute::updateOrCreate(
            ['slug' => 'atlyginimas-iki', 'category_id' => $categoryId],
            [
                'name' => 'Atlyginimas iki (€)',
                'type' => 'number',
                'is_required' => false,
                'filterable' => true,
                'position' => 6,
            ]
        );

        // Kalbos
        $kalbos = Attribute::updateOrCreate(
            ['slug' => 'kalbos', 'category_id' => $categoryId],
            [
                'name' => 'Reikalingos kalbos',
                'type' => 'multiselect',
                'is_required' => false,
                'filterable' => true,
                'position' => 7,
            ]
        );
        $this->createOptions($kalbos->id, ['Lietuvių', 'Anglų', 'Rusų', 'Lenkų', 'Vokiečių', 'Prancūzų', 'Ispanų', 'Italų']);
    }

    private function createPetAttributes($categoryId)
    {
        // Gyvūno tipas
        $tipas = Attribute::updateOrCreate(
            ['slug' => 'gyvuno-tipas', 'category_id' => $categoryId],
            [
                'name' => 'Gyvūno tipas',
                'type' => 'select',
                'is_required' => true,
                'filterable' => true,
                'position' => 1,
            ]
        );
        $this->createOptions($tipas->id, ['Šuo', 'Katė', 'Paukštis', 'Graužikas', 'Žuvis', 'Roplys', 'Arklys', 'Kita']);

        // Veislė
        Attribute::updateOrCreate(
            ['slug' => 'veisle', 'category_id' => $categoryId],
            [
                'name' => 'Veislė',
                'type' => 'text',
                'is_required' => false,
                'filterable' => true,
                'position' => 2,
            ]
        );

        // Amžius
        Attribute::updateOrCreate(
            ['slug' => 'amzius', 'category_id' => $categoryId],
            [
                'name' => 'Amžius',
                'type' => 'text',
                'is_required' => false,
                'filterable' => false,
                'position' => 3,
            ]
        );

        // Lytis
        $lytis = Attribute::updateOrCreate(
            ['slug' => 'lytis', 'category_id' => $categoryId],
            [
                'name' => 'Lytis',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 4,
            ]
        );
        $this->createOptions($lytis->id, ['Patinas', 'Patelė']);

        // Spalva
        Attribute::updateOrCreate(
            ['slug' => 'spalva-gyvunas', 'category_id' => $categoryId],
            [
                'name' => 'Spalva',
                'type' => 'text',
                'is_required' => false,
                'filterable' => false,
                'position' => 5,
            ]
        );

        // Skiepai
        $skiepai = Attribute::updateOrCreate(
            ['slug' => 'skiepai', 'category_id' => $categoryId],
            [
                'name' => 'Skiepai',
                'type' => 'select',
                'is_required' => false,
                'filterable' => true,
                'position' => 6,
            ]
        );
        $this->createOptions($skiepai->id, ['Yra', 'Nėra', 'Dalinai']);

        // Mikroschemė
        $mikro = Attribute::updateOrCreate(
            ['slug' => 'mikroscheme', 'category_id' => $categoryId],
            [
                'name' => 'Mikroschemė',
                'type' => 'select',
                'is_required' => false,
                'filterable' => false,
                'position' => 7,
            ]
        );
        $this->createOptions($mikro->id, ['Yra', 'Nėra']);

        // Pasas
        $pasas = Attribute::updateOrCreate(
            ['slug' => 'pasas', 'category_id' => $categoryId],
            [
                'name' => 'Veterinarinis pasas',
                'type' => 'select',
                'is_required' => false,
                'filterable' => false,
                'position' => 8,
            ]
        );
        $this->createOptions($pasas->id, ['Yra', 'Nėra']);
    }

    private function createOptions($attributeId, array $options)
    {
        foreach ($options as $index => $option) {
            AttributeOption::updateOrCreate(
                [
                    'attribute_id' => $attributeId,
                    'slug' => \Illuminate\Support\Str::slug($option),
                ],
                [
                    'value' => $option,
                    'position' => $index + 1,
                ]
            );
        }
    }
}
