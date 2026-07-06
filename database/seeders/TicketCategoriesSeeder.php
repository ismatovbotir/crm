<?php

namespace Database\Seeders;

use App\Models\Support\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Настройка ПО',                  'slug' => 'software-setup',       'sla_hours' => 24],
            ['name' => 'Замена оборудования',            'slug' => 'equipment-replacement', 'sla_hours' => 8],
            ['name' => 'Обучение',                       'slug' => 'training',              'sla_hours' => 48],
            ['name' => 'Гарантийный случай',             'slug' => 'warranty',              'sla_hours' => 4],
            ['name' => 'Подключение нового устройства',  'slug' => 'device-setup',          'sla_hours' => 24],
            ['name' => 'Прочее',                         'slug' => 'other',                 'sla_hours' => 48],
        ];

        foreach ($categories as $cat) {
            TicketCategory::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['is_active' => true]));
        }

        $this->command->info('✓ TicketCategories: ' . count($categories));
    }
}
