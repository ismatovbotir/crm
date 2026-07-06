<?php

namespace Tests\Feature\Tickets;

use App\Livewire\Portal\EquipmentRequests\CreateForm;
use App\Livewire\Portal\EquipmentRequests\Index;
use App\Livewire\Portal\EquipmentRequests\Show;
use App\Models\Customer\Customer;
use App\Models\Support\EquipmentRequest;
use App\Models\User;
use Database\Factories\EquipmentRequestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Portal-side self-service coverage for Module 6 (Equipment Request System).
 *
 * Covers the new App\Livewire\Portal\EquipmentRequests\{CreateForm,Index,Show}
 * components built to close the gap noted in the sibling EquipmentRequestTest
 * (admin-side) file: clients can now submit equipment requests from the
 * portal, per CLAUDE.md §1.5 Module 6.
 */
class EquipmentRequestPortalCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function makeClientUser(Customer $customer, string $role = 'client-user'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->customers()->attach($customer->id, ['role' => $role === 'client-admin' ? 'owner' : 'viewer']);

        return $user;
    }

    // ── Happy path ───────────────────────────────────────────────────────

    public function test_client_user_can_submit_equipment_request_via_portal_form(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer, 'client-user');

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен POS-терминал для нового магазина')
            ->set('description', 'Требуется 3 комплекта, желательно с чековым принтером.')
            ->set('budget', '15000000')
            ->set('needed_by', now()->addDays(14)->toDateString())
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('portal.equipment-requests.index'));

        $this->assertDatabaseHas('equipment_requests', [
            'customer_id' => $customer->id,
            'manager_id'  => null,
            'subject'     => 'Нужен POS-терминал для нового магазина',
            'description' => 'Требуется 3 комплекта, желательно с чековым принтером.',
            'status'      => 'submitted',
        ]);

        $request = EquipmentRequest::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame(15000000.0, (float) $request->budget);
    }

    public function test_client_admin_can_submit_equipment_request_via_portal_form(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer, 'client-admin');

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужны весы с печатью этикеток')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('equipment_requests', [
            'customer_id' => $customer->id,
            'subject'     => 'Нужны весы с печатью этикеток',
            'status'      => 'submitted',
        ]);
    }

    public function test_saved_request_dispatches_saved_event_and_resets_form(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен сканер штрихкодов')
            ->call('save')
            ->assertDispatched('equipment-request-saved')
            ->assertSet('subject', '')
            ->assertSet('description', '')
            ->assertSet('budget', '')
            ->assertSet('needed_by', '');
    }

    // ── Validation ───────────────────────────────────────────────────────

    public function test_subject_is_required(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', '')
            ->call('save')
            ->assertHasErrors(['subject' => 'required']);

        $this->assertDatabaseCount('equipment_requests', 0);
    }

    public function test_budget_must_be_numeric(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен принтер этикеток')
            ->set('budget', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['budget' => 'numeric']);

        $this->assertDatabaseCount('equipment_requests', 0);
    }

    public function test_budget_cannot_be_negative(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен принтер этикеток')
            ->set('budget', '-100')
            ->call('save')
            ->assertHasErrors(['budget' => 'min']);

        $this->assertDatabaseCount('equipment_requests', 0);
    }

    public function test_needed_by_must_be_a_valid_date(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен принтер этикеток')
            ->set('needed_by', 'not-a-date')
            ->call('save')
            ->assertHasErrors(['needed_by' => 'date']);

        $this->assertDatabaseCount('equipment_requests', 0);
    }

    // ── Ownership on create ─────────────────────────────────────────────

    /**
     * The created request must always be scoped to the acting user's own
     * customer, no matter how many other customers exist in the database —
     * customer_id is resolved server-side (auth()->user()->customers()->first())
     * and is never bound to a client-controllable Livewire property.
     */
    public function test_created_request_always_belongs_to_acting_users_own_customer_even_if_other_customers_exist(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        Customer::factory()->create(); // an unrelated customer that must never be targeted
        Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('subject', 'Нужен фискальный принтер')
            ->call('save');

        $request = EquipmentRequest::firstOrFail();
        $this->assertSame($myCustomer->id, $request->customer_id);
    }

    /**
     * CreateForm exposes no public `customer_id` (or similar) property, so
     * there is nothing for a malicious client to `set()` in order to redirect
     * the request to another company. Attempting to set it throws, proving
     * the property simply does not exist on the component.
     */
    public function test_customer_id_is_not_a_settable_public_property_on_create_form(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        $this->expectException(PublicPropertyNotFoundException::class);

        Livewire::actingAs($client)
            ->test(CreateForm::class)
            ->set('customer_id', $otherCustomer->id);
    }

    // ── Ownership on Show / Index ───────────────────────────────────────

    public function test_client_cannot_view_another_customers_equipment_request(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        $otherRequest = EquipmentRequestFactory::new()->create(['customer_id' => $otherCustomer->id]);

        Livewire::actingAs($client)
            ->test(Show::class, ['equipmentRequest' => $otherRequest])
            ->assertForbidden();
    }

    public function test_client_can_view_own_equipment_request(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        $myRequest = EquipmentRequestFactory::new()->create(['customer_id' => $myCustomer->id]);

        Livewire::actingAs($client)
            ->test(Show::class, ['equipmentRequest' => $myRequest])
            ->assertOk();
    }

    public function test_client_sees_only_own_equipment_requests_in_index(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        EquipmentRequestFactory::new()->create(['customer_id' => $myCustomer->id, 'subject' => 'Моя заявка на весы']);
        EquipmentRequestFactory::new()->create(['customer_id' => $otherCustomer->id, 'subject' => 'Чужая конфиденциальная заявка']);

        Livewire::actingAs($client)
            ->test(Index::class)
            ->assertSee('Моя заявка на весы')
            ->assertDontSee('Чужая конфиденциальная заявка');
    }

    // ── Route / middleware smoke checks ─────────────────────────────────

    public function test_guest_is_redirected_to_login_from_portal_equipment_requests_index(): void
    {
        $this->get('/portal/equipment-requests')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_portal_equipment_request_create(): void
    {
        $this->get('/portal/equipment-requests/create')->assertRedirect('/login');
    }

    public function test_internal_staff_cannot_access_portal_equipment_requests_index(): void
    {
        $this->seedRoles();
        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $this->actingAs($manager)->get('/portal/equipment-requests')->assertForbidden();
    }

    public function test_internal_staff_cannot_access_portal_equipment_request_create(): void
    {
        $this->seedRoles();
        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $this->actingAs($manager)->get('/portal/equipment-requests/create')->assertForbidden();
    }

    public function test_client_user_can_reach_portal_equipment_requests_index_route(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        $this->actingAs($client)->get('/portal/equipment-requests')->assertOk();
    }

    public function test_client_user_can_reach_portal_equipment_request_create_route(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);

        $this->actingAs($client)->get('/portal/equipment-requests/create')->assertOk();
    }
}
