<?php

namespace Tests\Feature\Tickets;

use App\Livewire\Admin\EquipmentRequests\Show as AdminShow;
use App\Livewire\Portal\EquipmentRequests\Show as PortalShow;
use App\Models\Customer\Customer;
use App\Models\Support\EquipmentRequestComment;
use App\Models\User;
use Database\Factories\EquipmentRequestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the client-visible comment thread on EquipmentRequest
 * (App\Models\Support\EquipmentRequestComment + Admin/Portal Show::addComment()),
 * mirroring the Ticket/TicketComment pattern.
 */
class EquipmentRequestCommentTest extends TestCase
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

    // ── Admin side ───────────────────────────────────────────────────────

    public function test_staff_can_post_an_internal_note(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($agent)
            ->test(AdminShow::class, ['equipmentRequest' => $request])
            ->set('commentBody', 'Внутренняя заметка: уточнить у склада наличие.')
            ->set('isInternal', true)
            ->call('addComment');

        $this->assertDatabaseHas('equipment_request_comments', [
            'equipment_request_id' => $request->id,
            'user_id'              => $agent->id,
            'body'                 => 'Внутренняя заметка: уточнить у склада наличие.',
            'is_internal'          => true,
        ]);
    }

    public function test_staff_can_post_a_public_reply(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($agent)
            ->test(AdminShow::class, ['equipmentRequest' => $request])
            ->set('commentBody', 'Добрый день! Уточните, пожалуйста, требуемое количество.')
            ->set('isInternal', false)
            ->call('addComment');

        $this->assertDatabaseHas('equipment_request_comments', [
            'equipment_request_id' => $request->id,
            'user_id'              => $agent->id,
            'body'                 => 'Добрый день! Уточните, пожалуйста, требуемое количество.',
            'is_internal'          => false,
        ]);
    }

    public function test_internal_comments_never_leak_into_public_comments_query_or_portal_view(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);
        $request = EquipmentRequestFactory::new()->create(['customer_id' => $customer->id]);

        EquipmentRequestComment::create([
            'equipment_request_id' => $request->id,
            'user_id'              => $agent->id,
            'body'                 => 'СЕКРЕТНАЯ внутренняя заметка про закупочную цену',
            'is_internal'          => true,
        ]);

        EquipmentRequestComment::create([
            'equipment_request_id' => $request->id,
            'user_id'              => $agent->id,
            'body'                 => 'Публичный ответ клиенту про сроки поставки',
            'is_internal'          => false,
        ]);

        $publicComments = $request->fresh()->publicComments()->get();
        $this->assertCount(1, $publicComments);
        $this->assertSame('Публичный ответ клиенту про сроки поставки', $publicComments->first()->body);

        Livewire::actingAs($client)
            ->test(PortalShow::class, ['equipmentRequest' => $request])
            ->assertDontSee('СЕКРЕТНАЯ внутренняя заметка про закупочную цену')
            ->assertSee('Публичный ответ клиенту про сроки поставки');
    }

    // ── Portal side ──────────────────────────────────────────────────────

    public function test_client_reply_is_always_created_as_public(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);
        $request = EquipmentRequestFactory::new()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($client)
            ->test(PortalShow::class, ['equipmentRequest' => $request])
            ->set('commentBody', 'Здравствуйте, нам нужно 5 комплектов, а не 3.')
            ->call('addComment');

        $this->assertDatabaseHas('equipment_request_comments', [
            'equipment_request_id' => $request->id,
            'user_id'              => $client->id,
            'body'                 => 'Здравствуйте, нам нужно 5 комплектов, а не 3.',
            'is_internal'          => false,
        ]);
    }

    /**
     * Portal Show has no client-facing toggle to mark a comment internal —
     * attempting to set such a property throws, proving there is no way for
     * a client to smuggle is_internal=true through the portal component.
     */
    public function test_portal_show_exposes_no_internal_toggle_property(): void
    {
        $this->seedRoles();
        $customer = Customer::factory()->create();
        $client = $this->makeClientUser($customer);
        $request = EquipmentRequestFactory::new()->create(['customer_id' => $customer->id]);

        $this->expectException(PublicPropertyNotFoundException::class);

        Livewire::actingAs($client)
            ->test(PortalShow::class, ['equipmentRequest' => $request])
            ->set('isInternal', true);
    }

    public function test_client_from_another_customer_cannot_reach_the_comment_form(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $client = $this->makeClientUser($myCustomer);

        $otherRequest = EquipmentRequestFactory::new()->create(['customer_id' => $otherCustomer->id]);

        Livewire::actingAs($client)
            ->test(PortalShow::class, ['equipmentRequest' => $otherRequest])
            ->assertForbidden();

        // Confirm no comment could have been posted through this blocked instance.
        $this->assertDatabaseCount('equipment_request_comments', 0);
    }
}
