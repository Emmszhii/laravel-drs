<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Asserts the actual per-role access matrix enforced by routes/web.php and
 * routes/api.php's `role:` middleware groups (Phase 1 of INTEGRATION.md's
 * RBAC feature — the 5 roles are kept as-is, just gated for real).
 */
class RoleGatingTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('readOnlyWebRoutes')]
    public function test_read_only_web_route_access(string $route, string $role, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get($route)->assertStatus($expectedStatus);
    }

    public static function readOnlyWebRoutes(): array
    {
        $routes = ['/incidents', '/resources', '/resources/types', '/depots', '/dispatch', '/evacuation-centers'];
        $allowed = ['admin', 'developer', 'coordinator', 'respondent'];
        $denied = ['viewer'];

        $cases = [];
        foreach ($routes as $route) {
            foreach ($allowed as $role) {
                $cases["{$route} as {$role}"] = [$route, $role, 200];
            }
            foreach ($denied as $role) {
                $cases["{$route} as {$role}"] = [$route, $role, 403];
            }
        }

        return $cases;
    }

    #[DataProvider('adminOnlyWebRoutes')]
    public function test_admin_only_web_route_access(string $route, string $role, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get($route)->assertStatus($expectedStatus);
    }

    public static function adminOnlyWebRoutes(): array
    {
        $routes = ['/reports', '/settings/users', '/settings/routing', '/settings/disaster-feed', '/settings/smtp'];
        $allowed = ['admin', 'developer'];
        $denied = ['coordinator', 'respondent', 'viewer'];

        $cases = [];
        foreach ($routes as $route) {
            foreach ($allowed as $role) {
                $cases["{$route} as {$role}"] = [$route, $role, 200];
            }
            foreach ($denied as $role) {
                $cases["{$route} as {$role}"] = [$route, $role, 403];
            }
        }

        return $cases;
    }

    public function test_public_dashboard_is_reachable_without_authentication(): void
    {
        $this->get('/public')->assertOk();
    }

    #[DataProvider('incidentCreatePayloads')]
    public function test_incident_creation_is_gated_by_role(string $role, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->postJson('/api/incidents', [
            'name' => 'Test Incident',
            'type' => 'flood',
            'signal' => 2,
            'zone' => 'Test Zone',
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertStatus($expectedStatus);
    }

    public static function incidentCreatePayloads(): array
    {
        return [
            'admin can create' => ['admin', 201],
            'developer can create' => ['developer', 201],
            'coordinator can create' => ['coordinator', 201],
            'respondent cannot create' => ['respondent', 403],
            'viewer cannot create' => ['viewer', 403],
        ];
    }

    /**
     * Regression guard: this endpoint used to be role:admin,developer-only
     * while the /incidents page itself (and view-incidents permission) also
     * grants coordinator/respondent read access — leaving those two roles
     * with a page they could open but a permanently empty incident list.
     */
    #[DataProvider('incidentPaginationSearchPayloads')]
    public function test_incident_pagination_search_matches_read_only_tier(string $role, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->getJson('/api/incidents/pagination-search?per_page=10&page=1')
            ->assertStatus($expectedStatus);
    }

    public static function incidentPaginationSearchPayloads(): array
    {
        return [
            'admin can list' => ['admin', 200],
            'developer can list' => ['developer', 200],
            'coordinator can list' => ['coordinator', 200],
            'respondent can list' => ['respondent', 200],
            'viewer cannot list' => ['viewer', 403],
        ];
    }

    /**
     * Regression guard: /incidents/{disaster}/optimize used to sit outside
     * any `role:` group (auth-only), letting any authenticated user —
     * including viewer — trigger the solver directly.
     */
    #[DataProvider('optimizationTriggerPayloads')]
    public function test_optimization_trigger_is_gated_by_role(string $role, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role]);
        $disaster = Disaster::factory()->create();

        $this->actingAs($user)->post("/incidents/{$disaster->id}/optimize")
            ->assertStatus($expectedStatus);
    }

    public static function optimizationTriggerPayloads(): array
    {
        return [
            'admin can trigger' => ['admin', 302],
            'developer can trigger' => ['developer', 302],
            'coordinator can trigger' => ['coordinator', 302],
            'respondent cannot trigger' => ['respondent', 403],
            'viewer cannot trigger' => ['viewer', 403],
        ];
    }

    /**
     * The position/status routes sit inside the same
     * role:admin,developer,coordinator,respondent group as the other
     * dispatch read routes (routes/api.php), so viewer can't reach them at
     * all — respondent is the lowest tier that can ever report a position,
     * even as the assigned driver. Above that floor, authorizeDriverOrCoordinator
     * additionally lets the assigned driver through without requiring
     * coordinator-tier.
     */
    public function test_assigned_driver_can_update_dispatch_position_even_as_respondent(): void
    {
        $driver = User::factory()->create(['role' => 'respondent']);
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id]);

        $this->actingAs($driver)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertOk();
    }

    public function test_viewer_cannot_reach_dispatch_position_route_even_as_assigned_driver(): void
    {
        $driver = User::factory()->create(['role' => 'viewer']);
        $dispatch = Dispatch::factory()->create(['driver_id' => $driver->id]);

        $this->actingAs($driver)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertStatus(403);
    }

    public function test_non_driver_non_coordinator_cannot_update_dispatch_position(): void
    {
        $bystander = User::factory()->create(['role' => 'respondent']);
        $dispatch = Dispatch::factory()->create();

        $this->actingAs($bystander)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertStatus(403);
    }

    public function test_coordinator_can_update_any_dispatch_position(): void
    {
        $coordinator = User::factory()->create(['role' => 'coordinator']);
        $dispatch = Dispatch::factory()->create();

        $this->actingAs($coordinator)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertOk();
    }

    /**
     * Regression guard for the legacy `role` column / Spatie roles drift bug
     * (see User::booted()) — every user, not just ones created through
     * Api\Settings\UserController, must end up with a matching Spatie role.
     */
    public function test_creating_a_user_automatically_syncs_its_spatie_role(): void
    {
        $user = User::factory()->create(['role' => 'coordinator']);

        $this->assertTrue($user->hasRole('coordinator'));
    }

    public function test_changing_a_users_role_resyncs_its_spatie_role(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $this->assertTrue($user->hasRole('viewer'));

        $user->update(['role' => 'admin']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('viewer'));
    }
}
