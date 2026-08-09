<?php

namespace App\Services\Reports;

/** Streams the same NdrrmcReportBuilder data as CSV rows via fputcsv. */
class NdrrmcCsvExporter
{
    /** @param array{disaster: \App\Models\Disaster, zones: \Illuminate\Support\Collection, evacuation_centers: \Illuminate\Support\Collection, summary: array, generated_at: \Illuminate\Support\Carbon} $data */
    public function stream(array $data): void
    {
        $out = fopen('php://output', 'w');

        fputcsv($out, ['NDRRMC SitRep', $data['disaster']->name]);
        fputcsv($out, ['Generated', $data['generated_at']->toDayDateTimeString()]);
        fputcsv($out, []);

        fputcsv($out, [
            'Area', 'Population affected', 'Severity',
            'Dead', 'Injured', 'Missing',
            'Families affected', 'Houses totally damaged', 'Houses partially damaged',
            'Estimated damage cost', 'Currency',
        ]);
        foreach ($data['zones'] as $zone) {
            fputcsv($out, [
                $zone->name, $zone->population_affected, $zone->severity_level,
                $zone->casualties_dead, $zone->casualties_injured, $zone->casualties_missing,
                $zone->families_affected, $zone->houses_totally_damaged, $zone->houses_partially_damaged,
                $zone->estimated_damage_cost, $zone->damage_cost_currency,
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Totals']);
        foreach ($data['summary'] as $key => $value) {
            fputcsv($out, [ucwords(str_replace('_', ' ', $key)), $value]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Evacuation centers', 'Address', 'Capacity', 'Families served', 'Individuals served', 'Status']);
        foreach ($data['evacuation_centers'] as $center) {
            fputcsv($out, [
                $center->name, $center->address, $center->capacity,
                $center->families_served, $center->individuals_served, $center->status,
            ]);
        }

        fclose($out);
    }
}
