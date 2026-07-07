<?php

namespace Tests\Feature\Leads;

use App\Livewire\Admin\Leads\EditForm;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadEditTest extends TestCase
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

    // ── Bug 1 regression: manager_id required, not nullable ─────────────────

    public function test_edit_form_rejects_null_manager_id_with_validation_error_not_a_db_crash(): void
    {
        $this->seedRoles();

        $director = User::factory()->create();
        $director->assignRole('sales-director');

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id'  => $manager->id,
            'source_id'   => $this->source()->id,
            // lost_reason set to a non-null string to sidestep the unrelated
            // EditForm::$lost_reason typed-property bug documented separately
            // in test_edit_form_mount_crashes_when_lead_has_no_lost_reason() below.
            'lost_reason' => 'other',
        ]);

        Livewire::actingAs($director)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->set('manager_id', null)
            ->call('save')
            ->assertHasErrors(['manager_id']);

        $this->assertSame($manager->id, $lead->fresh()->manager_id);
    }

    // ── Bug 2 regression: won_amount persists correctly ──────────────────────

    public function test_won_amount_is_persisted_when_status_is_won(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id'  => $manager->id,
            'source_id'   => $this->source()->id,
            'status'      => 'in_negotiation',
            'lost_reason' => 'other', // see note above re: EditForm::$lost_reason bug
        ]);

        Livewire::actingAs($manager)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->set('status', 'won')
            ->set('won_amount', '15000000.50')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('15000000.50', $lead->fresh()->won_amount);
        $this->assertSame('won', $lead->fresh()->status);
    }

    public function test_won_amount_stays_null_when_untouched_on_a_non_won_lead(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id'  => $manager->id,
            'source_id'   => $this->source()->id,
            'status'      => 'new',
            'won_amount'  => null,
            'lost_reason' => 'other', // see note above re: EditForm::$lost_reason bug
        ]);

        Livewire::actingAs($manager)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->call('save')
            ->assertHasNoErrors(['won_amount']);

        $this->assertNull($lead->fresh()->won_amount);
    }

    // ── NEW BUG (found while writing this coverage, not previously known):
    // `won_amount` rule is 'nullable|numeric|min:0'. Laravel's `nullable`
    // treats an empty string '' as "empty" and skips the `numeric` check for
    // it, so validate() happily returns `won_amount => ''`. But
    // `Lead::$casts['won_amount'] = 'decimal:2'` cannot cast an empty string
    // and throws "Unable to cast value to a decimal." on `$this->lead->update($data)`.
    // Reachable in practice: mark a lead 'won', type an amount, then clear the
    // input (wire:model sends '') and submit — EditForm::save() crashes instead
    // of persisting null. Left failing intentionally to document the bug; see
    // report for who should fix this (laravel-fullstack).

    public function test_clearing_won_amount_to_empty_string_crashes_on_save(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id'  => $manager->id,
            'source_id'   => $this->source()->id,
            'status'      => 'won',
            'won_amount'  => '1000000.00',
            'lost_reason' => 'other', // see note above re: EditForm::$lost_reason bug
        ]);

        Livewire::actingAs($manager)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->set('won_amount', '')
            ->call('save')
            ->assertHasNoErrors(['won_amount']);

        $this->assertNull($lead->fresh()->won_amount);
    }

    // ── NEW BUG (found while writing this coverage, not previously known):
    // EditForm::$lost_reason is declared as a non-nullable `string` property,
    // but the underlying `leads.lost_reason` column is nullable and defaults to
    // null for every lead that hasn't been marked 'lost' with a reason. mount()
    // does `$this->fill($this->lead->only([..., 'lost_reason', ...]))`, and
    // Livewire's fill() assigns null straight into the typed property, which
    // PHP rejects with a TypeError. In practice this means EditForm crashes for
    // the vast majority of real leads (anything that was never lost) the moment
    // someone opens the edit slide-over. Left failing intentionally to document
    // the bug; see report for details on who should fix this (laravel-fullstack).

    public function test_edit_form_mount_crashes_when_lead_has_no_lost_reason(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id'  => $manager->id,
            'source_id'   => $this->source()->id,
            'status'      => 'new',
            'lost_reason' => null,
        ]);

        Livewire::actingAs($manager)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->assertHasNoErrors();
    }

    // ── Bug 3 regression: converted ("client") leads are locked from editing ─

    public function test_edit_form_forbids_access_to_a_converted_lead(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id,
            'source_id'  => $this->source()->id,
            'status'     => 'client',
        ]);

        Livewire::actingAs($manager)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->assertForbidden();
    }

    public function test_sales_director_is_also_forbidden_from_editing_a_converted_lead(): void
    {
        $this->seedRoles();

        $director = User::factory()->create();
        $director->assignRole('sales-director');

        $lead = Lead::factory()->create([
            'manager_id' => User::factory()->create()->id,
            'source_id'  => $this->source()->id,
            'status'     => 'client',
        ]);

        Livewire::actingAs($director)
            ->test(EditForm::class, ['leadId' => $lead->id])
            ->assertForbidden();
    }
}
