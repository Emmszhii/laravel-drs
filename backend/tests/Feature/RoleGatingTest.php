<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Asserts the per-role access matrix enforced by routes/web.php and
 * routes/api.php's `role:` middleware groups (system-spec.md §2.1). Roles are
 * the 6 CDRRMO San Pablo City functional divisions plus `viewer`; the Admin
 * (`is_system_admin`, the Head of Office) passes every role check.
 */
class RoleGatingTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['viewer', 'incident_management', 'ert', 'logistics', 'operations', 'research_planning', 'admin_training'];

    #[DataProvider('readOnlyWebRoutes')]
    public function test_every_role_can_open_the_read_only_pages(string $route, string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get($route)->assertOk();
    }

    public static function readOnlyWebRoutes(): array
    {
        $cases = [];
        foreach (['/replenishment', '/stock', '/ics-requests', '/ics-requests/borrowed', '/resources/types', '/depots'] as $route) {
            foreach (self::ROLES as $role) {
                $cases["{$route} as {$role}"] = [$route, $role];
            }
        }

        return $cases;
    }

    public function test_home_redirects_to_the_replenishment_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'ert']))->get('/')->assertRedirect('/replenishment');
    }

    #[DataProvider('adminOnlyWebRoutes')]
    public function test_settings_are_admin_only(string $route, bool $isSystemAdmin, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => $isSystemAdmin]);

        $this->actingAs($user)->get($route)->assertStatus($expectedStatus);
    }

    public static function adminOnlyWebRoutes(): array
    {
        $cases = [];
        foreach (['/settings/users', '/settings/smtp'] as $route) {
            $cases["{$route} as admin"] = [$route, true, 200];
            $cases["{$route} as non-admin"] = [$route, false, 403];
        }

        return $cases;
    }

    /** The Admin can do everything any role can, including Logistics-only work. */
    public function test_admin_passes_logistics_only_routes(): void
    {
        $admin = User::factory()->create(['role' => 'research_planning', 'is_system_admin' => true]);
        $ert = User::factory()->create(['role' => 'ert']);

        $this->actingAs($admin)->getJson('/api/stock/conflicts')->assertOk();
        $this->actingAs($ert)->getJson('/api/stock/conflicts')->assertForbidden();
    }

    public function test_only_the_admin_runs_the_solver(): void
    {
        $logistics = User::factory()->create(['role' => 'logistics']);

        $this->actingAs($logistics)->postJson('/api/replenishment/runs', ['budget' => 1000])->assertForbidden();
    }

    #[DataProvider('catalogWriters')]
    public function test_catalog_is_kept_by_admin_and_training_and_the_admin(string $role, bool $isSystemAdmin, int $expectedStatus): void
    {
        $user = User::factory()->create(['role' => $role, 'is_system_admin' => $isSystemAdmin]);

        $this->actingAs($user)->postJson('/api/resource-types', ['name' => "Item {$role}", 'unit' => 'pieces'])->assertStatus($expectedStatus);
    }

    public static function catalogWriters(): array
    {
        return [
            'admin_training' => ['admin_training', false, 201],
            'admin with another role' => ['logistics', true, 201],
            'logistics' => ['logistics', false, 403],
            'ert' => ['ert', false, 403],
        ];
    }

    public function test_the_admin_gets_every_frontend_permission_and_role(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create(['role' => 'ert', 'is_system_admin' => true]);
        $ert = User::factory()->create(['role' => 'ert']);

        $this->assertContains('manage-depots', $admin->toInertiaPayload()['permissions']);
        $this->assertContains('logistics', $admin->toInertiaPayload()['roles']);
        $this->assertNotContains('manage-depots', $ert->toInertiaPayload()['permissions']);
    }

    public function test_non_admin_division_staff_cannot_reach_settings(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'is_system_admin' => false]);

        $this->actingAs($user)->get('/settings/users')->assertStatus(403);
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
