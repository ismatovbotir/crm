<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BusinessTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Магазин',         'slug' => 'shop'],
            ['name' => 'Супермаркет',     'slug' => 'supermarket'],
            ['name' => 'Ресторан',        'slug' => 'restaurant'],
            ['name' => 'Кафе',            'slug' => 'cafe'],
            ['name' => 'Аптека',          'slug' => 'pharmacy'],
            ['name' => 'Склад',           'slug' => 'warehouse'],
            ['name' => 'Другое',          'slug' => 'other'],
        ];

        foreach ($types as $i => $data) {
            BusinessType::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'sort_order' => $i, 'is_active' => true]
            );
        }

        $this->command->info('✓ BusinessTypes: '.count($types).' штук');
    }
}
