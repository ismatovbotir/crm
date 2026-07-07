<?php

namespace Tests\Feature\Leads;

use App\Livewire\Admin\Leads\Index;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function source(): LeadSource
    {
        return LeadSource::firstOrCreate(
            ['slug' => 'site'],
            ['name' => 'Сайт rsg.uz', 'is_active' => true, 'sort_order' => 1]
        );
    }

    // ── Ownership scoping ────────────────────────────────────────────────────
    // NOTE: the base "sales-manager only sees own leads" scenario is already
    // covered by tests\Feature\Access\AccessControlTest::test_leads_index_only_lists_own_leads_for_sales_manager.
    // Here we cover the remaining scoping gaps: director/super-admin see everything,
    // and the more nuanced search/status-filter interactions around converted leads.

    public function test_sales_director_sees_leads_from_all_managers(): void
    {
        $this->seedRoles();

        $managerA = User::factory()->create();
        $managerA->assignRole('sales-manager');
        $managerB = User::factory()->create();
        $managerB->assignRole('sales-manager');
        $director = User::factory()->create();
        $director->assignRole('sales-director');

        $source = $this->source();
        Lead::factory()->create(['manager_id' => $managerA->id, 'source_id' => $source->id, 'name' => 'Lead From A']);
        Lead::factory()->create(['manager_id' => $managerB->id, 'source_id' => $source->id, 'name' => 'Lead From B']);

        Livewire::actingAs($director)
            ->test(Index::class)
            ->assertSee('Lead From A')
            ->assertSee('Lead From B');
    }

    public function test_super_admin_sees_leads_from_all_managers(): void
    {
        $this->seedRoles();

        $managerA = User::factory()->create();
        $managerA->assignRole('sales-manager');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $source = $this->source();
        Lead::factory()->create(['manager_id' => $managerA->id, 'source_id' => $source->id, 'name' => 'Lead From A']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Lead From A');
    }

    // ── Search ───────────────────────────────────────────────────────────────

    public function test_search_matches_by_name_company_phone_and_email(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Азиз Каримов', 'company' => 'Магазин Ромашка',
            'phone' => '+998911112233', 'email' => 'aziz@example.com',
        ]);
        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Другой Лид', 'company' => 'Иная Компания',
            'phone' => '+998900000000', 'email' => 'other@example.com',
        ]);

        Livewire::actingAs($manager)->test(Index::class)
            ->set('search', 'Каримов')
            ->assertSee('Азиз Каримов')->assertDontSee('Другой Лид');

        Livewire::actingAs($manager)->test(Index::class)
            ->set('search', 'Ромашка')
            ->assertSee('Азиз Каримов')->assertDontSee('Другой Лид');

        Livewire::actingAs($manager)->test(Index::class)
            ->set('search', '+998911112233')
            ->assertSee('Азиз Каримов')->assertDontSee('Другой Лид');

        Livewire::actingAs($manager)->test(Index::class)
            ->set('search', 'aziz@example.com')
            ->assertSee('Азиз Каримов')->assertDontSee('Другой Лид');
    }

    // ── Regression: converted ("client") leads and search/status interaction ──

    public function test_converted_lead_is_findable_via_active_text_search(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Конвертированный Лид', 'phone' => '+998977778899',
            'status' => 'client',
        ]);

        Livewire::actingAs($manager)->test(Index::class)
            ->set('search', '+998977778899')
            ->assertSee('Конвертированный Лид');
    }

    public function test_converted_leads_are_excluded_by_default_with_no_search_or_filter(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Обычный Лид', 'status' => 'new',
        ]);
        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Конвертированный Лид', 'status' => 'client',
        ]);

        Livewire::actingAs($manager)->test(Index::class)
            ->assertSee('Обычный Лид')
            ->assertDontSee('Конвертированный Лид');
    }

    public function test_explicit_client_status_filter_returns_converted_leads(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Обычный Лид', 'status' => 'new',
        ]);
        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Конвертированный Лид', 'status' => 'client',
        ]);

        Livewire::actingAs($manager)->test(Index::class)
            ->set('statusFilter', 'client')
            ->assertSee('Конвертированный Лид')
            ->assertDontSee('Обычный Лид');
    }
}
