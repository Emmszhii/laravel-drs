<?php

namespace Tests\Feature;

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
        $routes = ['/incidents', '/resources', '/resources/types', '/depots'];
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
        $routes = ['/reports', '/settings/users'];
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
}
