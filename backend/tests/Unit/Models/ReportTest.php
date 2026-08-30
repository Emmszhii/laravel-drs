<?php

namespace Tests\Unit\Models;

use App\Models\Disaster;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_at_casts_to_datetime(): void
    {
        $report = Report::factory()->create(['generated_at' => '2026-01-15 10:00:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $report->generated_at);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $disaster = Disaster::factory()->create();

        $report = Report::create([
            'disaster_id' => $disaster->id,
            'name' => 'After-action summary',
            'type' => 'After-action summary',
            'generated_at' => now(),
        ]);

        $this->assertSame('After-action summary', $report->name);
        $this->assertSame($disaster->id, $report->disaster_id);
    }

    public function test_disaster_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $report = Report::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue($report->disaster->is($disaster));
    }
}
