<?php

namespace Tests\Unit\Models;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvacuationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_numeric_fields(): void
    {
        $center = EvacuationCenter::factory()->create([
            'lat' => '14.1',
            'lng' => '121.1',
            'capacity' => '500',
            'families_served' => '20',
            'individuals_served' => '80',
        ]);

        $this->assertIsFloat($center->lat);
        $this->assertIsFloat($center->lng);
        $this->assertIsInt($center->capacity);
        $this->assertIsInt($center->families_served);
        $this->assertIsInt($center->individuals_served);
    }

    public function test_disaster_relation(): void
    {
        $disaster = Disaster::factory()->create();
        $center = EvacuationCenter::factory()->create(['disaster_id' => $disaster->id]);

        $this->assertTrue($center->disaster->is($disaster));
    }

    public function test_zone_relation(): void
    {
        $zone = Zone::factory()->create();
        $center = EvacuationCenter::factory()->create(['zone_id' => $zone->id]);

        $this->assertTrue($center->zone->is($zone));
    }
}
