<?php

namespace Tests\Unit\Models;

use App\Models\AppLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $log = AppLogger::create([
            'action' => 'create',
            'loggable_type' => 'App\\Models\\Depot',
            'loggable_id' => 1,
            'subject_label' => 'Test Depot',
            'actor_id' => 5,
            'actor' => ['id' => 5, 'name' => 'Juan Dela Cruz'],
            'changes' => ['name' => ['old' => null, 'new' => 'Test Depot']],
            'description' => 'Created depot',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'url' => '/depots',
            'method' => 'POST',
        ]);

        $this->assertSame('create', $log->action);
        $this->assertSame('App\\Models\\Depot', $log->loggable_type);
        $this->assertSame(1, $log->loggable_id);
        $this->assertSame('Test Depot', $log->subject_label);
        $this->assertSame(5, $log->actor_id);
    }

    public function test_actor_and_changes_cast_to_array(): void
    {
        $log = AppLogger::create([
            'action' => 'update',
            'actor' => ['id' => 1, 'name' => 'Admin'],
            'changes' => ['status' => ['old' => 'OPEN', 'new' => 'CLOSED']],
        ]);

        $this->assertIsArray($log->actor);
        $this->assertSame(['id' => 1, 'name' => 'Admin'], $log->actor);
        $this->assertIsArray($log->changes);
        $this->assertSame(['status' => ['old' => 'OPEN', 'new' => 'CLOSED']], $log->changes);
    }

    public function test_survives_when_the_subject_it_describes_no_longer_exists(): void
    {
        // Deliberately no FK constraint — the log outlives the row it describes.
        $log = AppLogger::create([
            'action' => 'delete',
            'loggable_type' => 'App\\Models\\Depot',
            'loggable_id' => 999999,
        ]);

        $this->assertSame(999999, $log->loggable_id);
        $this->assertDatabaseHas('app_loggers', ['id' => $log->id]);
    }
}
