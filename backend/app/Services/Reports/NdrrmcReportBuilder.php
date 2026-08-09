<?php

namespace App\Services\Reports;

use App\Models\Disaster;
use Illuminate\Support\Carbon;

/**
 * Single normalized data shape consumed by both the PDF (dompdf blade view)
 * and CSV export, so the two formats can't drift apart — see
 * Api\ReportController::download()'s format=pdf|csv branch.
 */
class NdrrmcReportBuilder
{
    /** @return array{disaster: Disaster, zones: \Illuminate\Support\Collection, evacuation_centers: \Illuminate\Support\Collection, summary: array, generated_at: Carbon} */
    public function build(Disaster $disaster): array
    {
        $disaster->loadMissing(['zones', 'evacuationCenters']);

        return [
            'disaster' => $disaster,
            'zones' => $disaster->zones,
            'evacuation_centers' => $disaster->evacuationCenters,
            'summary' => $disaster->ndrrmcSummary(),
            'generated_at' => now(),
        ];
    }
}
