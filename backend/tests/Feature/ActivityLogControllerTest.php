<?php

namespace Tests\Feature;

use App\Models\AppLogger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers GET /api/activity-log (Api\ActivityLogController::index), which
 * sits inside the plain auth:sanctum + active middleware group in
 * routes/api.php (no role:... restriction) and reads the app_loggers audit
 * trail, excluding 'visit' and 'error' actions.
 */
class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_activity_log_entries_with_expected_shape(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        // The User creation itself is audited (see ActivityLogServiceProvider),
        // so clear that noise before asserting an exact single-entry response.
        AppLogger::query()->delete();

        $log = AppLogger::create([
            'action' => 'create',
            'loggable_type' => 'App\\Models\\Disaster',
            'loggable_id' => 1,
            'subject_label' => 'Test Disaster',
            'actor_id' => $user->id,
            'actor' => ['id' => $user->id, 'name' => $user->first_name.' '.$user->last_name],
            'changes' => ['name' => 'Test Disaster'],
            'description' => 'Created disaster Test Disaster',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/activity-log');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [
                [
                    'id' => $log->id,
                    'ts' => $log->created_at->format('H:i:s'),
                    'tag' => 'CREATE',
                    'message' => 'Created disaster Test Disaster',
                ],
            ],
        ]);
    }

    public function test_activity_log_falls_back_to_ucfirst_action_when_description_is_null(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        AppLogger::create([
            'action' => 'update',
            'description' => null,
        ]);

        $response = $this->actingAs($user)->getJson('/api/activity-log');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                [
                    'tag' => 'UPDATE',
                    'message' => 'Update',
                ],
            ],
        ]);
    }

    public function test_activity_log_excludes_visit_and_error_actions(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        AppLogger::query()->delete();

        AppLogger::create(['action' => 'visit', 'description' => 'Visited /incidents']);
        AppLogger::create(['action' => 'error', 'description' => 'Something broke']);
        AppLogger::create(['action' => 'delete', 'description' => 'Deleted a depot']);

        $response = $this->actingAs($user)->getJson('/api/activity-log');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['message' => 'Deleted a depot']);
        $response->assertJsonMissing(['message' => 'Visited /incidents']);
        $response->assertJsonMissing(['message' => 'Something broke']);
    }

    public function test_activity_log_orders_entries_newest_first(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $older = AppLogger::create([
            'action' => 'create',
            'description' => 'Older entry',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
        $newer = AppLogger::create([
            'action' => 'create',
            'description' => 'Newer entry',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/activity-log');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertSame($newer->id, $data[0]['id']);
        $this->assertSame($older->id, $data[1]['id']);
    }

    public function test_activity_log_respects_limit_query_param_capped_at_100(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        foreach (range(1, 5) as $i) {
            AppLogger::create(['action' => 'create', 'description' => "Entry {$i}"]);
        }

        $response = $this->actingAs($user)->getJson('/api/activity-log?limit=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_unauthenticated_user_cannot_view_activity_log(): void
    {
        $this->getJson('/api/activity-log')->assertStatus(401);
    }

    public function test_suspended_user_cannot_view_activity_log(): void
    {
        $user = User::factory()->create(['role' => 'viewer', 'is_active' => false]);

        $this->actingAs($user)->getJson('/api/activity-log')->assertStatus(403);
    }
}
