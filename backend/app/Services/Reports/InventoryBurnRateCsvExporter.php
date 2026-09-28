<?php

namespace App\Services\Reports;

/** Streams the same InventoryBurnRateReportBuilder data as CSV rows via fputcsv. */
class InventoryBurnRateCsvExporter
{
    public function stream(array $data): void
    {
        $out = fopen('php://output', 'w');

        fputcsv($out, ['Inventory Burn-Rate & Reorder Report']);
        fputcsv($out, ['Generated', $data['generated_at']->toDayDateTimeString()]);
        fputcsv($out, []);
        fputcsv($out, ['Depot', 'Resource type', 'Available', 'Reserved', 'Avg quarterly consumption', 'Suggested reorder', 'Note']);

        foreach ($data['rows'] as $row) {
            fputcsv($out, [
                $row['depot'],
                $row['resource_type'],
                $row['quantity_available'],
                $row['quantity_reserved'],
                $row['avg_quarterly_consumption'],
                $row['suggested_reorder'],
                $row['note'],
            ]);
        }

        fclose($out);
    }
}
