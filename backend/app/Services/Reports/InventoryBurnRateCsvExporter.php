<?php

namespace App\Services\Reports;

/** Streams the same InventoryBurnRateReportBuilder data as CSV rows via fputcsv. */
class InventoryBurnRateCsvExporter
{
    public function stream(array $data): void
    {
        $out = fopen('php://output', 'w');

        fputcsv($out, ['Quarterly Burn-Rate & Reorder Report', "{$data['year']} Q{$data['quarter']}"]);
        fputcsv($out, ['Generated', $data['generated_at']->toDayDateTimeString()]);
        fputcsv($out, ['Stock records fingerprint', $data['fingerprint'], $data['records_ok'] ? 'records intact' : 'CHECK FAILED']);
        fputcsv($out, ['Purchase plan', $data['plan'] ? "approved, budget {$data['plan']['budget']}, spends {$data['plan']['spent']}" : 'none approved for this quarter']);
        fputcsv($out, []);
        fputcsv($out, ['Item', 'Unit', 'On hand', 'Reserved', 'Burn rate / quarter', 'Quarters of cover', 'Target', 'Reorder', 'Reason']);

        foreach ($data['rows'] as $row) {
            fputcsv($out, [
                $row['resource_type'],
                $row['unit'],
                $row['on_hand'],
                $row['reserved'],
                $row['burn_rate'],
                $row['quarters_of_cover'],
                $row['target'],
                $row['reorder'],
                $row['reason'],
            ]);
        }

        fclose($out);
    }
}
