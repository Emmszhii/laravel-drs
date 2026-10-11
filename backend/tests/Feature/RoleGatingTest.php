<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Asserts the per-role access matrix enforced by routes/web.php and
 * routes/api.php's `role:` middleware groups (system-spec.md §2.1). Roles follow
 * the office structure confirmed in the 2026-09-03 interview (no unassigned role); the
 * Admin (`is_system_admin`, the Head of Office) passes every role check.
 */
class RoleGatingTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['administrative', 'logistics', 'operations', 'research_planning', 'admin_training'];

    #[DataProvider('readOnlyWebRoutes')]
    public function test_every_role_can_open_the_read_only_pages(string $route, string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)->get($route)->assertOk();
    }

    public static function readOnlyWebRoutes(): array
    {
        $cases = [];
        foreach (['/replenishment', '/replenishment/backtest', '/replenishment/inputs', '/report', '/stock', '/ics-requests', '/ics-requests/borrowed', '/resources/types', '/depots', '/divisions'] as $route) {
            foreach (self::ROLES as $role) {
                $cases["{$route} as {$role}"] = [$route, $role];
            }
        }

        return $cases;
    }

    public function test_home_redirects_to_the_replenishment_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'operations']))->get('/')->assertRedirect('/replenishment');
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
        $operations = User::factory()->create(['role' => 'operations']);

        $this->actingAs($admin)->getJson('/api/stock/conflicts')->assertOk();
        $this->actingAs($operations)->getJson('/api/stock/conflicts')->assertForbidden();
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
            'operations' => ['operations', false, 403],
            'administrative' => ['administrative', false, 403],
        ];
    }

    public function test_the_admin_gets_every_frontend_permission_and_role(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create(['role' => 'operations', 'is_system_admin' => true]);
        $operations = User::factory()->create(['role' => 'operations']);

        $this->assertContains('manage-depots', $admin->toInertiaPayload()['permissions']);
        $this->assertContains('logistics', $admin->toInertiaPayload()['roles']);
        $this->assertNotContains('manage-depots', $operations->toInertiaPayload()['permissions']);
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
        $user = User::factory()->create(['role' => 'administrative']);

        $this->assertTrue($user->hasRole('administrative'));
    }

    public function test_changing_a_users_role_resyncs_its_spatie_role(): void
    {
        $user = User::factory()->create(['role' => 'administrative']);
        $this->assertTrue($user->hasRole('administrative'));

        $user->update(['role' => 'admin_training']);

        $this->assertTrue($user->hasRole('admin_training'));
        $this->assertFalse($user->hasRole('administrative'));
    }

    /** Administrative is a read-only role: it opens every page and changes nothing. */
    public function test_administrative_is_read_only(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['role' => 'administrative']);

        $this->assertSame(['view-dashboard'], $user->toInertiaPayload()['permissions']);
        $this->actingAs($user)->getJson('/api/stock/levels')->assertOk();
        foreach ([
            ['post', '/api/ics-requests'], ['post', '/api/stock/sync'], ['post', '/api/stock/counts'],
            ['post', '/api/replenishment/history/import'], ['post', '/api/replenishment/runs'],
            ['post', '/api/resource-types'], ['post', '/api/depots'], ['get', '/api/replenishment/report'],
            ['get', '/api/stock/conflicts'], ['get', '/api/settings/users'],
        ] as [$method, $url]) {
            $this->actingAs($user)->json(strtoupper($method), $url)->assertForbidden();
        }
    }

    /** Requesters are Logistics and Operations and Warning (whose ERT teams file); everyone else is refused. */
    public function test_only_logistics_and_operations_file_requests(): void
    {
        foreach (['logistics' => true, 'operations' => true, 'research_planning' => false, 'admin_training' => false, 'administrative' => false] as $role => $allowed) {
            $status = $this->actingAs(User::factory()->create(['role' => $role]))->postJson('/api/ics-requests', [])->baseResponse->getStatusCode();

            $allowed ? $this->assertSame(422, $status, "{$role} should reach validation") : $this->assertSame(403, $status, "{$role} should be refused");
        }
    }

    /** ERT is a team inside Operations, Incident Management was never named, and every account has a division: none can be saved as a role. */
    #[DataProvider('removedRoles')]
    public function test_the_removed_roles_cannot_be_saved_through_the_settings_user_api(string $removed): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin_training', 'is_system_admin' => true]);
        $target = User::factory()->create(['role' => 'logistics']);
        $new = ['first_name' => 'A', 'last_name' => 'B', 'email' => 'new@example.test'];

        $this->actingAs($admin)->postJson('/api/settings/users', $new + ['role' => $removed])->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->actingAs($admin)->postJson("/api/settings/users/{$target->id}", ['role' => $removed])->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertSame('logistics', $target->fresh()->role);

        $this->actingAs($admin)->postJson('/api/settings/users', $new + ['role' => 'administrative'])->assertSuccessful();
        $this->assertSame('administrative', User::where('email', 'new@example.test')->value('role'));
    }

    public static function removedRoles(): array
    {
        return ['ert' => ['ert'], 'incident_management' => ['incident_management'], 'viewer' => ['viewer']];
    }

    public function test_the_removed_roles_are_not_seeded(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['logistics', 'operations', 'research_planning', 'administrative', 'admin_training'],
            Role::pluck('name')->all(),
        );
    }

    /** The 2026_10_03_000002 migration: old ERT and Incident Management accounts become Operations. */
    public function test_the_migration_moves_ert_and_incident_management_users_to_operations(): void
    {
        $ert = User::factory()->create(['role' => 'ert']);
        $im = User::factory()->create(['role' => 'incident_management']);
        $logistics = User::factory()->create(['role' => 'logistics']);
        $this->assertTrue($ert->hasRole('ert') && $im->hasRole('incident_management'));

        $this->runRoleMigration();

        $this->assertSame(['operations', 'operations', 'logistics'], [$ert->fresh()->role, $im->fresh()->role, $logistics->fresh()->role]);
        $this->assertTrue($ert->fresh()->hasRole('operations') && $im->fresh()->hasRole('operations'));
        $this->assertSame(['operations'], $ert->fresh()->getRoleNames()->all());
        $this->assertTrue($logistics->fresh()->hasRole('logistics'));
        $this->assertSame(0, Role::whereIn('name', ['ert', 'incident_management'])->count());
        $this->assertSame(0, DB::table('model_has_roles')->whereNotIn('role_id', Role::pluck('id'))->count());
    }

    public function test_the_migration_is_safe_on_an_empty_database_and_when_run_twice(): void
    {
        $this->runRoleMigration();

        User::factory()->create(['role' => 'ert']);
        $this->runRoleMigration();
        $this->runRoleMigration();

        $this->assertSame(0, User::whereIn('role', ['ert', 'incident_management'])->count());
        $this->assertSame(1, User::where('role', 'operations')->count());
        $this->assertSame(1, DB::table('model_has_roles')->count());
    }

    /** The 2026_10_11_000001 migration: viewer accounts become Administrative, and Administrative is the new default. */
    public function test_the_migration_moves_viewer_users_to_administrative(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $logistics = User::factory()->create(['role' => 'logistics']);

        (require database_path('migrations/2026_10_11_000001_remove_viewer_role.php'))->up();

        $this->assertSame(['administrative', 'logistics'], [$viewer->fresh()->role, $logistics->fresh()->role]);
        $this->assertSame(['administrative'], $viewer->fresh()->getRoleNames()->all());
        $this->assertSame(0, Role::where('name', 'viewer')->count());
        DB::table('users')->insert(['id' => (string) Str::uuid(), 'first_name' => 'N', 'last_name' => 'D', 'email' => 'default@example.test', 'password' => 'x']);
        $this->assertSame('administrative', User::where('email', 'default@example.test')->value('role'));
    }

    private function runRoleMigration(): void
    {
        (require database_path('migrations/2026_10_03_000002_replace_ert_and_incident_management_roles.php'))->up();
    }
}
