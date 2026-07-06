<?php

namespace Tests\Feature\Tickets;

use App\Livewire\Admin\EquipmentRequests\Show;
use App\Models\Quote\Quote;
use App\Models\Support\EquipmentRequest;
use App\Models\User;
use Database\Factories\EquipmentRequestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers App\Livewire\Admin\EquipmentRequests\Show::convertToQuote() —
 * real EquipmentRequest -> Quote conversion (previously a stub that just
 * redirected to the quotes index).
 */
class EquipmentRequestConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function techSupportAgent(): User
    {
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');

        return $agent;
    }

    public function test_converting_a_fresh_request_creates_a_draft_quote_and_flips_status_to_quoted(): void
    {
        $this->seedRoles();
        $agent = $this->techSupportAgent();
        $request = EquipmentRequestFactory::new()->create([
            'subject'     => 'Нужен комплект POS-оборудования',
            'description' => 'Кассовый аппарат, сканер и принтер чеков',
            'budget'      => 12000000,
            'status'      => 'submitted',
        ]);

        $component = Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request]);

        $component->call('convertToQuote');

        $this->assertSame(1, Quote::count());

        $quote = Quote::firstOrFail();

        $this->assertSame($request->id, $quote->equipment_request_id);
        $this->assertSame($request->customer_id, $quote->customer_id);
        $this->assertSame('draft', $quote->status);
        $this->assertMatchesRegularExpression('/^КП-\d{4}-\d{4}$/u', $quote->number);
        $this->assertNotEmpty($quote->notes);
        $this->assertStringContainsString('Нужен комплект POS-оборудования', $quote->notes);

        $this->assertSame('quoted', $request->fresh()->status);

        $component->assertRedirect(route('admin.quotes.edit', $quote));
    }

    public function test_converting_an_already_converted_request_does_not_create_a_duplicate_quote(): void
    {
        $this->seedRoles();
        $agent = $this->techSupportAgent();
        $request = EquipmentRequestFactory::new()->create(['status' => 'submitted']);

        $first = Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request]);
        $first->call('convertToQuote');

        $this->assertSame(1, Quote::count());
        $existingQuote = Quote::firstOrFail();

        // Fresh component instance (simulating navigating back to the same page)
        $second = Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request->fresh()]);
        $second->call('convertToQuote');

        $this->assertSame(1, Quote::count(), 'A second conversion must not create a duplicate Quote.');
        $second->assertRedirect(route('admin.quotes.edit', $existingQuote));
    }

    public function test_quote_manager_id_falls_back_to_the_requests_assigned_manager_when_present(): void
    {
        $this->seedRoles();
        $agent = $this->techSupportAgent();
        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $request = EquipmentRequestFactory::new()->create([
            'manager_id' => $manager->id,
            'status'     => 'submitted',
        ]);

        Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->call('convertToQuote');

        $quote = Quote::firstOrFail();
        $this->assertSame($manager->id, $quote->manager_id);
        $this->assertNotSame($agent->id, $quote->manager_id);
    }

    public function test_quote_manager_id_falls_back_to_acting_user_when_request_has_no_manager(): void
    {
        $this->seedRoles();
        $agent = $this->techSupportAgent();
        $request = EquipmentRequestFactory::new()->create([
            'manager_id' => null,
            'status'     => 'submitted',
        ]);

        Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->call('convertToQuote');

        $quote = Quote::firstOrFail();
        $this->assertSame($agent->id, $quote->manager_id);
    }

    public function test_role_without_equipment_requests_view_permission_cannot_reach_convert_action(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $this->assertFalse($sm->can('equipment-requests.view'));

        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($sm)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->assertForbidden();

        $this->assertSame(0, Quote::count());
    }
}
