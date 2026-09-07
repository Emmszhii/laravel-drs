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
 * routes/api.php's `role:` middleware groups. Roles are the 6 CDRRMO San
 * Pablo City functional divisions (see CLAUDE.md / RoleSeeder) rather than
 * the old admin/developer/coordinator/viewer/respondent placeholders;
 * system-configuration access is the separate `is_system_admin` flag.
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
        $routes = ['/incidents', '/resources', '/resources/types', '/resources/loans', '/requests', '/depots', '/dispatch', '/evacuation-centers'];
        $allowed = ['incident_management', 'ert', 'logistics', 'operations', 'research_planning', 'admin_training'];
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

    #[DataProvider('systemAdminOnlyWebRoutes')]
    public function test_system_admin_only_web_route_access(string $route, bool $isSystemAdmin, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => $isSystemAdmin]);

        $this->actingAs($user)->get($route)->assertStatus($expectedStatus);
    }

    public static function systemAdminOnlyWebRoutes(): array
    {
        $routes = ['/reports', '/settings/users', '/settings/routing', '/settings/disaster-feed', '/settings/smtp'];

        $cases = [];
        foreach ($routes as $route) {
            $cases["{$route} as system_admin"] = [$route, true, 200];
            $cases["{$route} as non-system_admin"] = [$route, false, 403];
        }

        return $cases;
    }

    public function test_non_system_admin_division_staff_cannot_reach_settings(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'is_system_admin' => false]);

        $this->actingAs($user)->get('/settings/users')->assertStatus(403);
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
            'incident_management can create' => ['incident_management', 201],
            'operations can create' => ['operations', 201],
            'admin_training can create' => ['admin_training', 201],
            'logistics cannot create' => ['logistics', 403],
            'ert cannot create' => ['ert', 403],
            'research_planning cannot create' => ['research_planning', 403],
            'viewer cannot create' => ['viewer', 403],
        ];
    }

    /**
     * Regression guard: this endpoint used to be admin-only while the
     * /incidents page itself (and view-incidents permission) also grants
     * every division read access — leaving read-only divisions with a page
     * they could open but a permanently empty incident list.
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
            'incident_management can list' => ['incident_management', 200],
            'ert can list' => ['ert', 200],
            'logistics can list' => ['logistics', 200],
            'operations can list' => ['operations', 200],
            'research_planning can list' => ['research_planning', 200],
            'admin_training can list' => ['admin_training', 200],
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
            'incident_management can trigger' => ['incident_management', 302],
            'operations can trigger' => ['operations', 302],
            'admin_training can trigger' => ['admin_training', 302],
            'logistics cannot trigger' => ['logistics', 403],
            'ert cannot trigger' => ['ert', 403],
            'research_planning cannot trigger' => ['research_planning', 403],
            'viewer cannot trigger' => ['viewer', 403],
        ];
    }

    /**
     * The position/status routes sit inside the same read-only role group as
     * the other dispatch read routes (routes/api.php), so viewer can't reach
     * them at all — `ert` is the lowest tier that can ever report a
     * position, even as the assigned driver. Above that floor,
     * authorizeDriverOrCoordinator additionally lets the assigned driver
     * through without requiring the coordinator-equivalent tier.
     */
    public function test_assigned_driver_can_update_dispatch_position_even_as_ert(): void
    {
        $driver = User::factory()->create(['role' => 'ert']);
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

    public function test_non_driver_non_coordinator_tier_cannot_update_dispatch_position(): void
    {
        $bystander = User::factory()->create(['role' => 'ert']);
        $dispatch = Dispatch::factory()->create();

        $this->actingAs($bystander)->postJson("/api/dispatches/{$dispatch->id}/position", [
            'lat' => 13.1,
            'lng' => 123.7,
        ])->assertStatus(403);
    }

    public function test_coordinator_tier_can_update_any_dispatch_position(): void
    {
        $coordinator = User::factory()->create(['role' => 'incident_management']);
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
        $user = User::factory()->create(['role' => 'incident_management']);

        $this->assertTrue($user->hasRole('incident_management'));
    }

    public function test_changing_a_users_role_resyncs_its_spatie_role(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $this->assertTrue($user->hasRole('viewer'));

        $user->update(['role' => 'admin_training']);

        $this->assertTrue($user->hasRole('admin_training'));
        $this->assertFalse($user->hasRole('viewer'));
    }
}
