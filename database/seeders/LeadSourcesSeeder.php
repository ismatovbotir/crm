<?php

namespace Database\Seeders;

use App\Models\Lead\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['name' => 'Сайт rsg.uz',        'slug' => 'site'],
            ['name' => 'Входящий звонок',    'slug' => 'incoming-call'],
            ['name' => 'Холодный звонок',    'slug' => 'cold-call'],
            ['name' => 'Реклама',            'slug' => 'ad'],
            ['name' => 'Выставка',           'slug' => 'exhibition'],
            ['name' => 'Рекомендация',       'slug' => 'referral'],
            ['name' => 'Другое',             'slug' => 'other'],
        ];

        foreach ($sources as $i => $data) {
            LeadSource::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'sort_order' => $i, 'is_active' => true]
            );
        }

        $this->command->info('✓ LeadSources: '.count($sources).' штук');
    }
}
