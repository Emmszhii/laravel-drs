<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Divisions and teams: Administration and Training keeps them; the ICS 213-RR form searches them. */
class DivisionTest extends TestCase
{
    use RefreshDatabase;

    private function keeper(): User
    {
        return User::factory()->create(['role' => 'admin_training']);
    }

    public function test_a_team_is_saved_under_a_division_and_labelled_with_it(): void
    {
        $ops = Division::create(['name' => 'Operations and Warning']);

        $this->actingAs($this->keeper())->postJson('/api/divisions', ['name' => 'Team Delta', 'parent_id' => $ops->id])
            ->assertCreated()->assertJsonPath('data.label', 'Operations and Warning / Team Delta');
    }

    public function test_search_matches_the_team_or_its_division(): void
    {
        $this->seed(CatalogSeeder::class);
        $user = User::factory()->create(['role' => 'operations']);

        $labels = fn (string $q) => collect($this->actingAs($user)->getJson("/api/divisions/pagination-search?search={$q}")->assertOk()->json('data.data'))->pluck('label')->all();

        $this->assertSame(['Operations and Warning / Team Delta'], $labels('delta'));
        $this->assertEqualsCanonicalizing(
            ['Operations and Warning', 'Operations and Warning / ERT', 'Operations and Warning / Team Delta'],
            $labels('operations'),
        );
    }

    public function test_teams_go_one_level_deep_and_names_are_unique(): void
    {
        $ops = Division::create(['name' => 'Operations and Warning']);
        $team = Division::create(['name' => 'Team Delta', 'parent_id' => $ops->id]);

        $this->actingAs($this->keeper())->postJson('/api/divisions', ['name' => 'Squad 1', 'parent_id' => $team->id])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->actingAs($this->keeper())->postJson('/api/divisions', ['name' => 'Team Delta'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->actingAs($this->keeper())->postJson("/api/divisions/{$ops->id}", ['parent_id' => Division::create(['name' => 'Administrative'])->id])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    }

    public function test_a_division_with_teams_cannot_be_removed(): void
    {
        $ops = Division::create(['name' => 'Operations and Warning']);
        $team = Division::create(['name' => 'Team Delta', 'parent_id' => $ops->id]);

        $this->actingAs($this->keeper())->deleteJson("/api/divisions/{$ops->id}")->assertUnprocessable();
        $this->actingAs($this->keeper())->deleteJson("/api/divisions/{$team->id}")->assertSuccessful();
        $this->actingAs($this->keeper())->deleteJson("/api/divisions/{$ops->id}")->assertSuccessful();
    }

    public function test_only_administration_and_training_edits_divisions(): void
    {
        foreach (['logistics', 'operations', 'research_planning', 'administrative'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->postJson('/api/divisions', ['name' => 'X'])->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['is_system_admin' => true]))->postJson('/api/divisions', ['name' => 'X'])->assertCreated();
    }
}
