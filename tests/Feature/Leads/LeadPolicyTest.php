<?php

namespace Tests\Feature\Leads;

use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use App\Policies\LeadPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPolicyTest extends TestCase
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

    public function test_super_admin_and_sales_director_bypass_ownership_for_view_update_delete(): void
    {
        $this->seedRoles();

        $policy = new LeadPolicy();
        $someoneElsesManager = User::factory()->create();

        $lead = Lead::factory()->create([
            'manager_id' => $someoneElsesManager->id,
            'source_id'  => $this->source()->id,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $director = User::factory()->create();
        $director->assignRole('sales-director');

        foreach ([$admin, $director] as $user) {
            $this->assertTrue($policy->view($user, $lead));
            $this->assertTrue($policy->update($user, $lead));
            $this->assertTrue($policy->delete($user, $lead));
        }
    }

    public function test_sales_manager_is_allowed_only_on_own_leads_for_view_update_delete(): void
    {
        $this->seedRoles();

        $policy = new LeadPolicy();

        $owner = User::factory()->create();
        $owner->assignRole('sales-manager');
        $stranger = User::factory()->create();
        $stranger->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id' => $owner->id,
            'source_id'  => $this->source()->id,
        ]);

        $this->assertTrue($policy->view($owner, $lead));
        $this->assertTrue($policy->update($owner, $lead));
        $this->assertTrue($policy->delete($owner, $lead));

        $this->assertFalse($policy->view($stranger, $lead));
        $this->assertFalse($policy->update($stranger, $lead));
        $this->assertFalse($policy->delete($stranger, $lead));
    }

    public function test_tech_support_has_no_lead_permissions_at_all(): void
    {
        $this->seedRoles();

        $policy = new LeadPolicy();

        $techSupport = User::factory()->create();
        $techSupport->assignRole('tech-support');

        $lead = Lead::factory()->create([
            'manager_id' => $techSupport->id, // even "owning" it shouldn't matter, no base permission
            'source_id'  => $this->source()->id,
        ]);

        $this->assertFalse($policy->viewAny($techSupport));
        $this->assertFalse($policy->create($techSupport));
        $this->assertFalse($policy->view($techSupport, $lead));
        $this->assertFalse($policy->update($techSupport, $lead));
        $this->assertFalse($policy->delete($techSupport, $lead));
    }
}
