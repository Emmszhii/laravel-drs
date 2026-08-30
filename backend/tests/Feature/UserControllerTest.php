<?php

namespace Tests\Feature;

use App\Mail\NewAccountMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_users_with_expected_fields_and_hides_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'role' => 'coordinator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/settings/users')->assertOk();

        $response->assertJsonPath('success', true);

        $payload = collect($response->json('data'));
        $entry = $payload->firstWhere('id', $target->id);

        $this->assertNotNull($entry);
        $this->assertSame('Jane', $entry['first_name']);
        $this->assertSame('Doe', $entry['last_name']);
        $this->assertSame('Jane Doe', $entry['name']);
        $this->assertSame('jane.doe@example.com', $entry['email']);
        $this->assertSame('coordinator', $entry['role']);
        $this->assertTrue($entry['is_active']);
        $this->assertArrayNotHasKey('password', $entry);
        $this->assertArrayNotHasKey('remember_token', $entry);
    }

    public function test_pagination_search_filters_by_search_term(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['first_name' => 'Zendaya', 'last_name' => 'Reyes', 'email' => 'zendaya@example.com']);
        User::factory()->create(['first_name' => 'Marco', 'last_name' => 'Polo', 'email' => 'marco@example.com']);

        $response = $this->actingAs($admin)
            ->getJson('/api/settings/users/pagination-search?search=Zendaya&per_page=10&page=1')
            ->assertOk();

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertSame('Zendaya', $data[0]['first_name']);

        $meta = $response->json('data.meta');
        $this->assertSame(1, $meta['current_page']);
        $this->assertSame(1, $meta['total']);
        $this->assertArrayHasKey('last_page', $meta);
        $this->assertArrayHasKey('per_page', $meta);
        $this->assertArrayHasKey('from', $meta);
        $this->assertArrayHasKey('to', $meta);
    }

    public function test_pagination_search_filters_by_exact_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'respondent']);
        User::factory()->create(['role' => 'viewer']);

        $response = $this->actingAs($admin)
            ->getJson('/api/settings/users/pagination-search?filters[role]=respondent&per_page=10&page=1')
            ->assertOk();

        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        foreach ($data as $row) {
            $this->assertSame('respondent', $row['role']);
        }
    }

    public function test_store_creates_a_user_with_no_password_and_sends_invite_mail(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/settings/users', [
            'first_name' => 'New',
            'last_name' => 'Hire',
            'email' => 'new.hire@example.com',
            'role' => 'respondent',
            'is_active' => true,
        ])->assertStatus(201);

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Record created successfully.');
        $response->assertJsonPath('data.first_name', 'New');
        $response->assertJsonPath('data.email', 'new.hire@example.com');
        $response->assertJsonPath('data.role', 'respondent');

        $this->assertDatabaseHas('users', [
            'email' => 'new.hire@example.com',
            'first_name' => 'New',
            'last_name' => 'Hire',
            'role' => 'respondent',
        ]);

        $created = User::where('email', 'new.hire@example.com')->firstOrFail();
        $this->assertNull($created->password);
        $this->assertTrue($created->hasRole('respondent'));

        Mail::assertSent(NewAccountMail::class, fn ($mail) => $mail->hasTo('new.hire@example.com'));
    }

    public function test_store_fails_validation_with_duplicate_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $existing = User::factory()->create(['email' => 'dupe@example.com']);

        $response = $this->actingAs($admin)->postJson('/api/settings/users', [
            'first_name' => 'Another',
            'last_name' => 'Person',
            'email' => 'dupe@example.com',
            'role' => 'viewer',
        ])->assertStatus(422);

        $response->assertJsonValidationErrors('email');
        $this->assertSame(1, User::where('email', 'dupe@example.com')->count());
    }

    public function test_store_fails_validation_when_required_fields_are_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson('/api/settings/users', [
            'email' => 'incomplete@example.com',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'role']);
    }

    public function test_update_persists_changed_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'role' => 'viewer',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/settings/users/{$target->id}", [
            'first_name' => 'Updated',
            'role' => 'coordinator',
        ])->assertOk();

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Record updated successfully.');
        $response->assertJsonPath('data.first_name', 'Updated');
        $response->assertJsonPath('data.role', 'coordinator');
        $response->assertJsonPath('data.last_name', 'Name');

        $fresh = $target->fresh();
        $this->assertSame('Updated', $fresh->first_name);
        $this->assertSame('Name', $fresh->last_name);
        $this->assertSame('coordinator', $fresh->role);
        $this->assertTrue($fresh->hasRole('coordinator'));
    }

    public function test_update_hashes_a_new_password_when_provided(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        $originalPassword = $target->password;

        $this->actingAs($admin)->postJson("/api/settings/users/{$target->id}", [
            'password' => 'brand-new-password',
        ])->assertOk();

        $fresh = $target->fresh();
        $this->assertNotSame($originalPassword, $fresh->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('brand-new-password', $fresh->password));
    }

    public function test_update_fails_validation_with_duplicate_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['email' => 'target@example.com']);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->postJson("/api/settings/users/{$target->id}", [
            'email' => 'taken@example.com',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertSame('target@example.com', $target->fresh()->email);
    }

    public function test_destroy_removes_the_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/settings/users/{$target->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->deleteJson("/api/settings/users/{$admin->id}")
            ->assertStatus(422);

        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', "You can't delete your own account.");

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
