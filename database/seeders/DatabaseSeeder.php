<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core — run in all environments
        $this->call([
            RolesSeeder::class,

            BusinessTypesSeeder::class,
            BanksSeeder::class,
            LeadSourcesSeeder::class,

            ProductGroupsSeeder::class,
            CatalogSeeder::class,
            ProductSeeder::class,
            BusinessTypeRecommendationsSeeder::class,
            TicketCategoriesSeeder::class,
        ]);

        // Demo data — local / dev only
        if (app()->environment(['local', 'development'])) {
            $this->call([
                DemoUsersSeeder::class,
                DemoLeadsSeeder::class,
            ]);
        }
    }
}
