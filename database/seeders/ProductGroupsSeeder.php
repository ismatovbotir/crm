<?php

namespace Database\Seeders;

use App\Models\Catalog\ProductGroup;
use Illuminate\Database\Seeder;

class ProductGroupsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name_ru'    => 'POS-оборудование',
                'name_uz'    => 'POS-uskunalar',
                'color'      => 'blue',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'name_ru'    => 'Торговое оборудование',
                'name_uz'    => 'Savdo uskunalari',
                'color'      => 'green',
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'name_ru'    => 'POS-материалы',
                'name_uz'    => 'POS-materiallar',
                'color'      => 'orange',
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'name_ru'    => 'Расходные материалы',
                'name_uz'    => 'Sarflanadigan materiallar',
                'color'      => 'gray',
                'sort_order' => 4,
                'is_active'  => true,
            ],
        ];

        foreach ($groups as $data) {
            ProductGroup::updateOrCreate(
                ['name_ru' => $data['name_ru']],
                $data
            );
        }

        $this->command->info('✓ ProductGroups: ' . count($groups) . ' created/updated');
    }
}
