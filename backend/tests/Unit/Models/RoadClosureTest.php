<?php

namespace Tests\Unit\Models;

use App\Models\RoadClosure;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadClosureTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts(): void
    {
        $closure = RoadClosure::factory()->create([
            'lat' => '14.1',
            'lng' => '121.1',
            'radius_m' => '150',
            'active_from' => '2026-01-01 00:00:00',
            'active_until' => '2026-01-02 00:00:00',
            'is_active' => 1,
        ]);

        $this->assertIsFloat($closure->lat);
        $this->assertIsFloat($closure->lng);
        $this->assertIsInt($closure->radius_m);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $closure->active_from);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $closure->active_until);
        $this->assertIsBool($closure->is_active);
        $this->assertTrue($closure->is_active);
    }

    public function test_created_by_relation(): void
    {
        $user = User::factory()->create();
        $closure = RoadClosure::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($closure->createdBy->is($user));
    }

    public function test_is_active_at_is_false_when_flag_is_off(): void
    {
        $closure = RoadClosure::factory()->create(['is_active' => false]);

        $this->assertFalse($closure->isActiveAt(now()));
    }

    public function test_is_active_at_is_true_with_no_time_window(): void
    {
        $closure = RoadClosure::factory()->create([
            'is_active' => true,
            'active_from' => null,
            'active_until' => null,
        ]);

        $this->assertTrue($closure->isActiveAt(now()->subYears(5)));
        $this->assertTrue($closure->isActiveAt(now()->addYears(5)));
    }

    public function test_is_active_at_respects_active_from_lower_bound(): void
    {
        $closure = RoadClosure::factory()->create([
            'is_active' => true,
            'active_from' => now()->addDay(),
            'active_until' => null,
        ]);

        $this->assertFalse($closure->isActiveAt(now()));
        $this->assertTrue($closure->isActiveAt(now()->addDays(2)));
    }

    public function test_is_active_at_respects_active_until_upper_bound(): void
    {
        $closure = RoadClosure::factory()->create([
            'is_active' => true,
            'active_from' => null,
            'active_until' => now()->addDay(),
        ]);

        $this->assertTrue($closure->isActiveAt(now()));
        $this->assertFalse($closure->isActiveAt(now()->addDays(2)));
    }
}
