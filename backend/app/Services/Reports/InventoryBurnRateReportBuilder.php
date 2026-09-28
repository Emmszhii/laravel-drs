<?php

namespace App\Services\Reports;

use App\Models\Resource;
use App\Services\ReorderSuggestionCalculator;
use Illuminate\Support\Carbon;

/**
 * Single normalized data shape consumed by both the PDF (dompdf blade view)
 * and CSV export, so the two formats can't drift apart — see
 * Api\ReportController::download()'s format=pdf|csv branch. Fulfills the
 * study's original "inventory burn-rate reports... exportable to PDF and
 * CSV" proposal, replacing the earlier NDRRMC SitRep report.
 */
class InventoryBurnRateReportBuilder
{
    public function __construct(private readonly ReorderSuggestionCalculator $calculator)
    {
    }

    /** @return array{rows: \Illuminate\Support\Collection, generated_at: Carbon} */
    public function build(): array
    {
        $resources = Resource::with(['depot', 'resourceType'])->get();

        $rows = $resources->map(function (Resource $resource) {
            $suggestion = $this->calculator->suggest($resource);

            return [
                'depot' => $resource->depot->name ?? 'Unknown depot',
                'resource_type' => $resource->resourceType->name ?? 'Unknown resource type',
                'quantity_available' => $resource->quantity_available,
                'quantity_reserved' => $resource->quantity_reserved,
                'avg_quarterly_consumption' => $suggestion['avg_quarterly_consumption'],
                'suggested_reorder' => $suggestion['suggested_reorder'],
                'note' => $suggestion['note'],
            ];
        });

        return [
            'rows' => $rows,
            'generated_at' => now(),
        ];
    }
}
