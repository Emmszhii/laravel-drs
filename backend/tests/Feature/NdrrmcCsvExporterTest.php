<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\EvacuationCenter;
use App\Models\Zone;
use App\Services\Reports\NdrrmcCsvExporter;
use App\Services\Reports\NdrrmcReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NdrrmcCsvExporterTest extends TestCase
{
    use RefreshDatabase;

    protected function captureCsv(array $data): array
    {
        ob_start();
        (new NdrrmcCsvExporter())->stream($data);
        $csv = ob_get_clean();

        $rows = array_map('str_getcsv', preg_split('/\r\n|\n|\r/', trim($csv)));

        return $rows;
    }

    public function test_it_writes_the_expected_headers_and_rows_for_seeded_data(): void
    {
        $disaster = Disaster::factory()->create(['name' => 'Typhoon Test']);

        $zone = Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'name' => 'Brgy. Test',
            'population_affected' => 1200,
            'severity_level' => 'High',
            'casualties_dead' => 2,
            'casualties_injured' => 5,
            'casualties_missing' => 1,
            'families_affected' => 100,
            'houses_totally_damaged' => 10,
            'houses_partially_damaged' => 20,
            'estimated_damage_cost' => 50000,
            'damage_cost_currency' => 'PHP',
        ]);

        $center = EvacuationCenter::factory()->create([
            'disaster_id' => $disaster->id,
            'name' => 'Test Gym',
            'address' => '123 Main St, Test City',
            'capacity' => 500,
            'families_served' => 80,
            'individuals_served' => 300,
            'status' => 'OPEN',
        ]);

        $data = app(NdrrmcReportBuilder::class)->build($disaster->fresh());

        $rows = $this->captureCsv($data);

        $this->assertSame(['NDRRMC SitRep', 'Typhoon Test'], $rows[0]);
        $this->assertStringStartsWith('Generated', $rows[1][0]);
        $this->assertSame([null], $rows[2]);

        $this->assertSame([
            'Area', 'Population affected', 'Severity',
            'Dead', 'Injured', 'Missing',
            'Families affected', 'Houses totally damaged', 'Houses partially damaged',
            'Estimated damage cost', 'Currency',
        ], $rows[3]);

        $this->assertSame([
            'Brgy. Test', '1200', 'High',
            '2', '5', '1',
            '100', '10', '20',
            '50000', 'PHP',
        ], $rows[4]);

        // blank row, then "Totals" section
        $this->assertSame([null], $rows[5]);
        $this->assertSame(['Totals'], $rows[6]);

        $summaryRows = [];
        $i = 7;
        while ($rows[$i] !== [null]) {
            $summaryRows[] = $rows[$i];
            $i++;
        }

        $summaryByLabel = collect($summaryRows)->mapWithKeys(fn ($r) => [$r[0] => $r[1]]);
        $this->assertSame('2', $summaryByLabel['Casualties Dead']);
        $this->assertSame('5', $summaryByLabel['Casualties Injured']);
        $this->assertSame('1', $summaryByLabel['Casualties Missing']);
        $this->assertSame('100', $summaryByLabel['Families Affected']);
        $this->assertSame('10', $summaryByLabel['Houses Totally Damaged']);
        $this->assertSame('20', $summaryByLabel['Houses Partially Damaged']);
        $this->assertSame('50000', $summaryByLabel['Estimated Damage Cost']);

        $i++; // skip blank row
        $this->assertSame(
            ['Evacuation centers', 'Address', 'Capacity', 'Families served', 'Individuals served', 'Status'],
            $rows[$i]
        );
        $i++;
        $this->assertSame([
            'Test Gym', '123 Main St, Test City', '500', '80', '300', 'OPEN',
        ], $rows[$i]);
    }

    public function test_it_escapes_commas_and_quotes_in_text_fields(): void
    {
        $disaster = Disaster::factory()->create(['name' => 'Flood, "Big" One']);

        Zone::factory()->create([
            'disaster_id' => $disaster->id,
            'name' => 'Brgy. "San, Jose"',
        ]);

        EvacuationCenter::factory()->create([
            'disaster_id' => $disaster->id,
            'name' => 'Gym, Annex "B"',
            'address' => '1 Rd, Suite "A", Town',
        ]);

        $data = app(NdrrmcReportBuilder::class)->build($disaster->fresh());

        ob_start();
        (new NdrrmcCsvExporter())->stream($data);
        $csv = ob_get_clean();

        // fputcsv wraps fields containing commas/quotes in double quotes and
        // doubles internal quotes — assert the raw CSV text reflects that.
        $this->assertStringContainsString('"Flood, ""Big"" One"', $csv);
        $this->assertStringContainsString('"Brgy. ""San, Jose"""', $csv);
        $this->assertStringContainsString('"Gym, Annex ""B"""', $csv);
        $this->assertStringContainsString('"1 Rd, Suite ""A"", Town"', $csv);

        // And round-trip correctly back through str_getcsv.
        $rows = array_map('str_getcsv', preg_split('/\r\n|\n|\r/', trim($csv)));
        $this->assertSame(['NDRRMC SitRep', 'Flood, "Big" One'], $rows[0]);
        $this->assertSame('Brgy. "San, Jose"', $rows[4][0]);
    }
}
