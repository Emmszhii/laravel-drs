<?php

namespace Tests\Feature;

use Database\Seeders\CatalogSeeder;
use App\Models\Borrow;
use App\Models\Depot;
use App\Models\IcsRequest;
use App\Models\Kit;
use App\Models\ResourceType;
use App\Models\StockEntry;
use App\Models\User;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The fields and records the office's paper ICS 213-RR form and borrow logbook carry
 * (photos of 2026-09-03): control no., time, requester name and position, subject
 * category, per-line reasons, the Logistics reply, walk-in loans to other offices,
 * kits, and the real item catalog.
 */
class IcsFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    private ResourceType $gauze;

    private ResourceType $cutter;

    private Depot $depot;

    private User $operations;

    private User $admin;

    private User $logistics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gauze = ResourceType::factory()->create(['name' => 'Gauze 4x4', 'unit' => 'boxes', 'category' => 'medical']);
        $this->cutter = ResourceType::factory()->create(['name' => 'Bolt cutter', 'unit' => 'units', 'is_consumable' => false]);
        $this->depot = Depot::factory()->create();
        $ledger = app(StockLedger::class);
        $ledger->record(['resource_type_id' => $this->gauze->id, 'depot_id' => $this->depot->id, 'quantity' => 50, 'reason' => 'receipt'], null);
        $ledger->record(['resource_type_id' => $this->cutter->id, 'depot_id' => $this->depot->id, 'quantity' => 5, 'reason' => 'receipt'], null);
        $this->operations = User::factory()->create(['role' => 'operations', 'position' => 'Team Leader']);
        $this->admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true, 'position' => 'Head of Office']);
        $this->logistics = User::factory()->create(['role' => 'logistics']);
    }

    private function onHand(ResourceType $t): int
    {
        return app(StockLedger::class)->level($t->id);
    }

    private function request(array $extra = [], ?User $as = null)
    {
        return $this->actingAs($as ?? $this->operations)->postJson('/api/ics-requests', $extra + [
            'requesting_division' => 'Operations / Team Delta', 'purpose' => 'team_kit',
            'lines' => [['resource_type_id' => $this->gauze->id, 'quantity_requested' => 10]],
        ]);
    }

    public function test_an_item_has_one_of_the_forms_subject_categories(): void
    {
        $keeper = User::factory()->create(['role' => 'admin_training']);

        $id = $this->actingAs($keeper)->postJson('/api/resource-types', ['name' => 'Face mask', 'unit' => 'boxes', 'category' => 'medical'])
            ->assertCreated()->json('data.id');
        $this->assertSame('medical', ResourceType::find($id)->category);

        $plain = $this->actingAs($keeper)->postJson('/api/resource-types', ['name' => 'Rope', 'unit' => 'meters'])->assertCreated()->json('data.id');
        $this->assertSame('general', ResourceType::find($plain)->fresh()->category);

        $this->actingAs($keeper)->postJson('/api/resource-types', ['name' => 'Snacks', 'unit' => 'packs', 'category' => 'snacks'])
            ->assertStatus(422)->assertJsonValidationErrors('category');
    }

    public function test_a_request_keeps_the_forms_control_number_time_and_requester(): void
    {
        $id = $this->request(['control_no' => '0040', 'time' => '06:00', 'requester_name' => 'AJ Bogyne', 'requester_position' => 'Team Delta member'])
            ->assertCreated()->json('id');

        $r = IcsRequest::find($id);
        $this->assertSame(['0040', '06:00', 'AJ Bogyne', 'Team Delta member', 'Operations / Team Delta'],
            [$r->control_no, substr($r->time, 0, 5), $r->requester_name, $r->requester_position, $r->requesting_division]);

        $this->request(['control_no' => '0040'])->assertStatus(422)->assertJsonValidationErrors('control_no');
    }

    public function test_requester_name_position_and_time_default_to_the_logged_in_user_and_now(): void
    {
        $this->travelTo(now()->setTime(14, 30));
        $r = IcsRequest::find($this->request()->assertCreated()->json('id'));

        $this->assertSame([$this->operations->name, 'Team Leader', '14:30'], [$r->requester_name, $r->requester_position, substr($r->time, 0, 5)]);
        $this->assertNull($r->control_no); // optional: only the printed form number goes here
    }

    public function test_a_reduced_approval_needs_a_reason_for_that_line(): void
    {
        $r = IcsRequest::with('lines')->find($this->request(['lines' => [
            ['resource_type_id' => $this->gauze->id, 'quantity_requested' => 10],
            ['resource_type_id' => $this->cutter->id, 'quantity_requested' => 2],
        ]])->assertCreated()->json('id'));
        [$gauzeLine, $cutterLine] = $r->lines->all();
        $pick = fn (array $g, array $c) => ['lines' => [$gauzeLine->id => $g + ['depot_id' => $this->depot->id], $cutterLine->id => $c + ['depot_id' => $this->depot->id]]];

        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/approve", $pick(['quantity_approved' => 6], ['quantity_approved' => 2]))
            ->assertStatus(422)->assertJsonValidationErrors('lines');
        $this->assertSame('submitted', $r->fresh()->status); // nothing was approved

        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$r->id}/approve", $pick(['quantity_approved' => 6, 'reason' => 'Only 6 boxes are left in the kit'], ['quantity_approved' => 2]))
            ->assertOk();
        $this->assertSame(['Only 6 boxes are left in the kit', null], [$gauzeLine->fresh()->approval_note, $cutterLine->fresh()->approval_note]);
    }

    public function test_logistics_writes_the_reply_remarks_and_requesters_cannot(): void
    {
        $id = $this->request()->assertCreated()->json('id');

        $this->actingAs($this->operations)->postJson("/api/ics-requests/{$id}/remarks", ['remarks' => 'x'])->assertForbidden();
        $this->actingAs($this->logistics)->postJson("/api/ics-requests/{$id}/remarks", ['remarks' => '4 items from #1 backpack - released'])->assertOk();

        $this->assertSame('4 items from #1 backpack - released', IcsRequest::find($id)->remarks);
    }

    public function test_equipment_is_lent_without_a_request_to_another_office_and_comes_back(): void
    {
        $lend = fn (array $extra = [], ?User $as = null) => $this->actingAs($as ?? $this->logistics)->postJson('/api/borrows', $extra + [
            'resource_type_id' => $this->cutter->id, 'depot_id' => $this->depot->id, 'quantity' => 1,
            'borrower_division' => 'CGSO', 'borrower_name' => 'Peter', 'location' => 'CGSO',
        ]);

        $lend([], $this->operations)->assertForbidden();
        $lend(['resource_type_id' => $this->gauze->id])->assertStatus(422); // consumables go through a request
        $lend(['borrower_name' => ''])->assertStatus(422)->assertJsonValidationErrors('borrower_name');
        $this->assertSame(5, $this->onHand($this->cutter));

        $lend()->assertCreated();

        $b = Borrow::sole();
        $this->assertSame([null, 'CGSO', 'Peter', 'CGSO', 'issued'], [$b->request_line_id, $b->borrower_division, $b->borrower_name, $b->location, $b->status]);
        $this->assertSame(4, $this->onHand($this->cutter));
        $this->assertSame(['issue'], StockEntry::where('borrow_id', $b->id)->pluck('reason')->all());
        $this->assertSame(['issued'], $b->history()->pluck('to_status')->all());

        $this->actingAs($this->logistics)->postJson("/api/borrows/{$b->id}/status", ['status' => 'returned'])->assertOk();
        $this->assertSame(5, $this->onHand($this->cutter));
    }

    public function test_lending_several_units_makes_one_logbook_row_each(): void
    {
        $this->actingAs($this->logistics)->postJson('/api/borrows', [
            'resource_type_id' => $this->cutter->id, 'depot_id' => $this->depot->id, 'quantity' => 2,
            'borrower_division' => 'CGSO', 'borrower_name' => 'Peter', 'property_numbers' => ['PR-1', 'PR-2'],
        ])->assertCreated();

        $this->assertSame(['PR-1', 'PR-2'], Borrow::orderBy('property_number')->pluck('property_number')->all());
        $this->assertSame(3, $this->onHand($this->cutter));
    }

    public function test_a_walk_in_loan_recorded_offline_syncs_with_its_office_and_location(): void
    {
        $borrowId = (string) Str::uuid();
        $issue = ['device_id' => (string) Str::uuid(), 'resource_type_id' => $this->cutter->id, 'depot_id' => $this->depot->id, 'quantity' => -1,
            'reason' => 'issue', 'borrow_id' => $borrowId, 'borrower_name' => 'Peter', 'borrower_division' => 'CGSO', 'location' => 'CGSO',
            'created_at_device' => now()->toIso8601String(), 'device' => 'phone-1'];

        $this->actingAs($this->logistics)->postJson('/api/stock/sync', ['entries' => [$issue]])->assertOk()->assertJsonPath('results.0.status', 'accepted');

        $b = Borrow::find($borrowId);
        $this->assertSame([null, 'CGSO', 'CGSO', 'issued'], [$b->request_line_id, $b->borrower_division, $b->location, $b->status]);
        $this->assertSame(4, $this->onHand($this->cutter));
    }

    public function test_logistics_saves_replaces_and_removes_a_kit_and_others_cannot(): void
    {
        $line = fn (ResourceType $t, int $q) => ['resource_type_id' => $t->id, 'quantity' => $q];

        $this->actingAs($this->operations)->postJson('/api/kits', ['name' => 'Medical backpack #1', 'lines' => [$line($this->gauze, 1)]])->assertForbidden();
        $this->actingAs($this->logistics)->postJson('/api/kits', ['name' => 'Medical backpack #1', 'lines' => [$line($this->gauze, 0)]])->assertStatus(422);

        $this->actingAs($this->logistics)->postJson('/api/kits', ['name' => 'Medical backpack #1', 'lines' => [$line($this->gauze, 1), $line($this->cutter, 1)]])->assertCreated();
        $this->assertSame(2, Kit::sole()->lines()->count());

        $this->actingAs($this->logistics)->postJson('/api/kits', ['name' => 'Medical backpack #1', 'lines' => [$line($this->gauze, 3)]])->assertCreated();
        $this->assertSame([3], Kit::sole()->lines()->pluck('quantity')->all()); // same name: replaced, not duplicated

        $this->actingAs($this->logistics)->deleteJson('/api/kits/'.Kit::sole()->id)->assertNoContent();
        $this->assertSame(0, Kit::count());
    }

    public function test_the_requests_page_shows_kits_and_who_acted_with_their_position(): void
    {
        $id = $this->request()->assertCreated()->json('id');
        $line = IcsRequest::with('lines')->find($id)->lines[0];
        $this->actingAs($this->admin)->postJson("/api/ics-requests/{$id}/approve", ['lines' => [$line->id => ['quantity_approved' => 10, 'depot_id' => $this->depot->id]]])->assertOk();
        $this->actingAs($this->logistics)->postJson('/api/kits', ['name' => 'Kit', 'lines' => [['resource_type_id' => $this->gauze->id, 'quantity' => 1]]])->assertCreated();

        $this->actingAs($this->admin)->get('/ics-requests')->assertOk()->assertInertia(function ($page) {
            $props = $page->toArray()['props'];
            $this->assertSame(['Kit'], array_column($props['kits'], 'name'));
            $this->assertContains('Head of Office', array_map(fn ($h) => $h['user']['position'] ?? null, $props['requests'][0]['history']));
            $this->assertSame('medical', $props['requests'][0]['lines'][0]['resource_type']['category']);
        });
    }

    public function test_the_borrowed_page_offers_equipment_and_depots_to_lend_from(): void
    {
        $this->actingAs($this->logistics)->get('/ics-requests/borrowed')->assertOk()->assertInertia(function ($page) {
            $props = $page->toArray()['props'];
            $this->assertSame(['Bolt cutter'], array_column($props['equipment'], 'name')); // consumables are not lent out
            $this->assertSame([$this->depot->id], array_column($props['depots'], 'id'));
        });
    }

    public function test_the_admin_sets_a_users_position_title(): void
    {
        $this->actingAs($this->admin)->postJson("/api/settings/users/{$this->logistics->id}", ['position' => 'Logistics Officer'])->assertOk();

        $this->assertSame('Logistics Officer', $this->logistics->fresh()->position);
    }

    public function test_the_catalog_seeder_loads_the_forms_items_their_kind_and_the_backpack_kit(): void
    {
        $this->seed(CatalogSeeder::class);
        $item = fn (string $name) => ResourceType::where('name', $name)->firstOrFail();

        $this->assertSame(['medical', true], [$item('Gauze 4x4')->category, $item('Gauze 4x4')->is_consumable]);
        $this->assertSame(['office', true], [$item('Black ballpen')->category, $item('Black ballpen')->is_consumable]);
        foreach (['Thermal gun', 'Blood pressure apparatus', 'Pulse oximeter', 'Bolt cutter'] as $equipment) {
            $this->assertFalse($item($equipment)->is_consumable, "$equipment is returned, so it is borrowed equipment");
        }

        $kit = Kit::where('name', 'Medical backpack #1')->firstOrFail();
        $this->assertGreaterThan(25, $kit->lines()->count());
        $this->assertSame(1, $kit->lines()->where('resource_type_id', $item('Gloves (L)')->id)->value('quantity'));
        $this->seed(CatalogSeeder::class); // safe to re-run
        $this->assertSame(1, Kit::where('name', 'Medical backpack #1')->count());
    }
}
