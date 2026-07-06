<?php

namespace Database\Seeders;

use App\Models\Customer\Customer;
use App\Models\Lead\Lead;
use Illuminate\Database\Seeder;

class DemoLeadsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('DemoLeadsSeeder пропущен (не local environment)');
            return;
        }

        // 10 customers
        Customer::factory(10)->create();
        Customer::factory(2)->vip()->create();

        // 30 лидов в разных статусах
        Lead::factory(30)->create();

        $this->command->info('✓ Customers: 12 (включая 2 VIP)');
        $this->command->info('✓ Leads: 30');
    }
}
