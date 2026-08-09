<?php

namespace Tests\Feature;

use App\Models\Disaster;
use App\Models\Report;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_ndrrmc_sitrep_downloads_as_pdf_by_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);
        $report = Report::factory()->create([
            'disaster_id' => $disaster->id,
            'type' => 'NDRRMC SitRep',
            'name' => 'NDRRMC SitRep',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get("/api/reports/{$report->id}/download");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_ndrrmc_sitrep_downloads_as_csv_when_requested(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        Zone::factory()->create(['disaster_id' => $disaster->id]);
        $report = Report::factory()->create([
            'disaster_id' => $disaster->id,
            'type' => 'NDRRMC SitRep',
            'name' => 'NDRRMC SitRep',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get("/api/reports/{$report->id}/download?format=csv");

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_other_report_types_still_download_as_plain_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $disaster = Disaster::factory()->create();
        $report = Report::factory()->create([
            'disaster_id' => $disaster->id,
            'type' => 'After-action summary',
            'name' => 'After-action summary',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get("/api/reports/{$report->id}/download");

        $response->assertOk();
        $this->assertStringStartsWith('text/plain', $response->headers->get('Content-Type'));
    }
}
