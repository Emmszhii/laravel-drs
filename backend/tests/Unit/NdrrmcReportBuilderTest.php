<?php

namespace Tests\Unit;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use App\Models\Zone;
use App\Services\Reports\NdrrmcReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NdrrmcReportBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_sums_zone_level_figures_across_the_disaster(): void
    {
        $disaster = Disaster::factory()->create();

        Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'casualties_dead' => 2,
            'casualties_injured' => 5,
            'casualties_missing' => 1,
            'families_affected' => 100,
            'houses_totally_damaged' => 10,
            'houses_partially_damaged' => 20,
            'estimated_damage_cost' => 50000,
        ]);
        Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'casualties_dead' => 0,
            'casualties_injured' => 3,
            'casualties_missing' => 0,
            'families_affected' => 40,
            'houses_totally_damaged' => 2,
            'houses_partially_damaged' => 8,
            'estimated_damage_cost' => 15000,
        ]);
        EvacuationCenter::factory()->create(['disaster_id' => $disaster->id]);

        $data = app(NdrrmcReportBuilder::class)->build($disaster->fresh());

        $this->assertSame(2, $data['summary']['casualties_dead']);
        $this->assertSame(8, $data['summary']['casualties_injured']);
        $this->assertSame(1, $data['summary']['casualties_missing']);
        $this->assertSame(140, $data['summary']['families_affected']);
        $this->assertSame(12, $data['summary']['houses_totally_damaged']);
        $this->assertSame(28, $data['summary']['houses_partially_damaged']);
        $this->assertSame(65000.0, $data['summary']['estimated_damage_cost']);
        $this->assertCount(2, $data['zones']);
        $this->assertCount(1, $data['evacuation_centers']);
    }
}
