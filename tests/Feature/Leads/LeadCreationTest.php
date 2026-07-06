<?php

namespace Tests\Feature\Leads;

use App\Livewire\Admin\Leads\CreateForm;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    public function test_sales_manager_can_create_a_lead(): void
    {
        $this->seedRoles();

        $source = LeadSource::create(['name' => 'Сайт rsg.uz', 'slug' => 'site', 'is_active' => true]);

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        Livewire::actingAs($manager)
            ->test(CreateForm::class)
            ->set('name', 'Азиз Каримов')
            ->set('phone', '+998901234567')
            ->set('email', 'aziz@example.com')
            ->set('company', 'Магазин Азиз')
            ->set('source_id', $source->id)
            ->set('status', 'new')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name'    => 'Азиз Каримов',
            'phone'   => '+998901234567',
            'company' => 'Магазин Азиз',
        ]);

        $lead = Lead::where('phone', '+998901234567')->firstOrFail();
        // sales-manager without explicit manager_id selection is auto-assigned to himself (see CreateForm::mount)
        $this->assertSame($manager->id, $lead->manager_id);
    }

    public function test_lead_creation_requires_name_and_phone(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        Livewire::actingAs($manager)
            ->test(CreateForm::class)
            ->set('name', '')
            ->set('phone', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'phone' => 'required']);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_lead_creation_rejects_invalid_email(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        Livewire::actingAs($manager)
            ->test(CreateForm::class)
            ->set('name', 'Test Contact')
            ->set('phone', '+998900000000')
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_user_without_leads_create_permission_is_denied(): void
    {
        $this->seedRoles();

        // tech-support role has no leads.create permission (see config/permissions.php)
        $techSupport = User::factory()->create();
        $techSupport->assignRole('tech-support');

        Livewire::actingAs($techSupport)
            ->test(CreateForm::class)
            ->assertForbidden();
    }
}
