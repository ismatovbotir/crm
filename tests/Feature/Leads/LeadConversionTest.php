<?php

namespace Tests\Feature\Leads;

use App\Livewire\Admin\Leads\Show;
use App\Models\BusinessType;
use App\Models\Customer\Customer;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadActivity;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadConversionTest extends TestCase
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

    public function test_converting_a_lead_creates_customer_contact_and_updates_lead(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $businessType = BusinessType::create(['name' => 'Магазин', 'slug' => 'store', 'is_active' => true]);

        $lead = Lead::factory()->create([
            'manager_id'       => $manager->id,
            'source_id'        => $this->source()->id,
            'name'             => 'Азиз Каримов',
            'company'          => 'ООО Ромашка',
            'phone'            => '+998901112233',
            'email'            => 'aziz@example.com',
            'business_type_id' => $businessType->id,
            'status'           => 'in_negotiation',
        ]);

        $this->assertDatabaseCount('customers', 0);

        Livewire::actingAs($manager)
            ->test(Show::class, ['lead' => $lead])
            ->call('openConvertForm')
            ->assertSet('convertName', 'ООО Ромашка')
            ->assertSet('convertPhone', '+998901112233')
            ->assertSet('convertEmail', 'aziz@example.com')
            ->call('convertToCustomer')
            ->assertHasNoErrors();

        $customer = Customer::first();
        $this->assertNotNull($customer);
        $this->assertSame('ООО Ромашка', $customer->name);
        $this->assertSame($businessType->id, $customer->business_type_id);
        $this->assertSame('active', $customer->status);
        $this->assertSame(today()->toDateString(), $customer->customer_since->toDateString());

        $contact = $customer->contacts()->first();
        $this->assertNotNull($contact);
        $this->assertTrue((bool) $contact->is_primary);
        $this->assertSame('Азиз Каримов', $contact->name);
        $this->assertSame('+998901112233', $contact->phone);
        $this->assertSame('aziz@example.com', $contact->email);

        $lead->refresh();
        $this->assertSame($customer->id, $lead->customer_id);
        $this->assertSame('client', $lead->status);
        $this->assertNotNull($lead->converted_at);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type'    => 'conversion',
        ]);
    }

    public function test_converting_an_already_converted_lead_is_rejected_and_does_not_duplicate_customer(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id,
            'source_id'  => $this->source()->id,
        ]);

        // First conversion.
        Livewire::actingAs($manager)
            ->test(Show::class, ['lead' => $lead])
            ->call('openConvertForm')
            ->call('convertToCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('customers', 1);

        $lead->refresh();

        // Second attempt on the now-converted lead.
        Livewire::actingAs($manager)
            ->test(Show::class, ['lead' => $lead])
            ->call('openConvertForm')
            ->call('convertToCustomer')
            ->assertStatus(422);

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_sales_manager_cannot_convert_another_managers_lead(): void
    {
        $this->seedRoles();

        // NOTE: Show::mount() already authorizes 'view' on the lead (ownership-gated,
        // same manager_id check as 'update'), so an intruder sales-manager is blocked
        // right at component instantiation before openConvertForm/convertToCustomer
        // ever run. This is the correct/expected behavior — there is no role in
        // config/permissions.php that holds leads.view without leads.update (or vice
        // versa), so 'view' and 'update' ownership can't be exercised independently
        // for this role. This test documents that the boundary holds end-to-end.
        $owner = User::factory()->create();
        $owner->assignRole('sales-manager');
        $intruder = User::factory()->create();
        $intruder->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id' => $owner->id,
            'source_id'  => $this->source()->id,
        ]);

        Livewire::actingAs($intruder)
            ->test(Show::class, ['lead' => $lead])
            ->assertForbidden();

        $this->assertDatabaseCount('customers', 0);
    }
}
