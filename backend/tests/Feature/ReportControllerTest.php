<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers ReportController::index/paginationSearch/store/destroy. `download`
 * is covered by ReportDownloadTest — not duplicated here.
 */
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_reports_with_their_disaster(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create(['name' => 'Typhoon Odette']);
        $report = Report::factory()->create([
            'disaster_id' => $disaster->id,
            'type' => 'Resource utilization',
            'name' => 'Resource utilization',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/reports');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.id', $report->id);
        $response->assertJsonPath('data.0.type', 'Resource utilization');
        $response->assertJsonPath('data.0.disaster.id', $disaster->id);
        $response->assertJsonPath('data.0.disaster.name', 'Typhoon Odette');
    }

    public function test_index_orders_by_generated_at_descending(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $older = Report::factory()->create(['generated_at' => now()->subDays(2)]);
        $newer = Report::factory()->create(['generated_at' => now()]);

        $response = $this->actingAs($admin)->getJson('/api/reports');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $newer->id);
        $response->assertJsonPath('data.1.id', $older->id);
    }

    public function test_index_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);

        $this->actingAs($coordinator)->getJson('/api/reports')->assertStatus(403);
    }

    public function test_pagination_search_filters_by_search_term_on_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Report::factory()->create(['name' => 'After-action summary', 'type' => 'After-action summary']);
        $match = Report::factory()->create(['name' => 'Response timeline', 'type' => 'Response timeline']);

        $response = $this->actingAs($admin)->getJson('/api/reports/pagination-search?search=Response&per_page=10&page=1');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_filters_by_search_term_on_related_disaster_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = Disaster::factory()->create(['name' => 'Typhoon Kristine']);
        $other = Disaster::factory()->create(['name' => 'Flood — Marikina']);
        $match = Report::factory()->create(['disaster_id' => $target->id]);
        Report::factory()->create(['disaster_id' => $other->id]);

        $response = $this->actingAs($admin)->getJson('/api/reports/pagination-search?search=Kristine&per_page=10&page=1');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_filters_by_exact_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $match = Report::factory()->create(['type' => 'NDRRMC SitRep', 'name' => 'NDRRMC SitRep']);
        Report::factory()->create(['type' => 'After-action summary', 'name' => 'After-action summary']);

        $response = $this->actingAs($admin)->getJson('/api/reports/pagination-search?'.http_build_query([
            'filters' => ['type' => 'NDRRMC SitRep'],
            'per_page' => 10,
            'page' => 1,
        ]));

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame($match->id, $data[0]['id']);
    }

    public function test_pagination_search_returns_correct_pagination_meta(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Report::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/reports/pagination-search?per_page=2&page=1');

        $response->assertOk();
        $response->assertJsonPath('data.meta.current_page', 1);
        $response->assertJsonPath('data.meta.per_page', 2);
        $response->assertJsonPath('data.meta.total', 3);
        $response->assertJsonPath('data.meta.last_page', 2);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_pagination_search_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);

        $this->actingAs($coordinator)->getJson('/api/reports/pagination-search?per_page=10&page=1')
            ->assertStatus(403);
    }

    public function test_store_creates_a_report_row_with_expected_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/reports', [
            'disaster_id' => $disaster->id,
            'type' => 'After-action summary',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Record created successfully.');
        $response->assertJsonPath('data.disaster_id', $disaster->id);
        $response->assertJsonPath('data.type', 'After-action summary');
        $response->assertJsonPath('data.name', 'After-action summary');
        $this->assertNotNull($response->json('data.generated_at'));

        $this->assertDatabaseHas('app_reports', [
            'disaster_id' => $disaster->id,
            'type' => 'After-action summary',
            'name' => 'After-action summary',
        ]);
    }

    public function test_store_fails_validation_for_invalid_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/reports', [
            'disaster_id' => $disaster->id,
            'type' => 'Not a real type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_store_fails_validation_for_missing_disaster(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/reports', [
            'disaster_id' => (string) Str::uuid(),
            'type' => 'After-action summary',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['disaster_id']);
    }

    public function test_store_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $disaster = Disaster::factory()->create();

        $this->actingAs($coordinator)->postJson('/api/reports', [
            'disaster_id' => $disaster->id,
            'type' => 'After-action summary',
        ])->assertStatus(403);
    }

    public function test_destroy_removes_the_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $report = Report::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/reports/{$report->id}");

        $response->assertStatus(204);
        $response->assertNoContent();
        $this->assertDatabaseMissing('app_reports', ['id' => $report->id]);
    }

    public function test_destroy_returns_404_for_missing_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->deleteJson('/api/reports/'.Str::uuid())
            ->assertStatus(404);
    }

    public function test_destroy_is_gated_to_admin_and_developer(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $report = Report::factory()->create();

        $this->actingAs($coordinator)->deleteJson("/api/reports/{$report->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('app_reports', ['id' => $report->id]);
    }
}
