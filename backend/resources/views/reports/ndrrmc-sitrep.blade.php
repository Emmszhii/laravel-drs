<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    h1 { font-size: 18px; margin-bottom: 2px; }
    h2 { font-size: 13px; margin-top: 20px; margin-bottom: 6px; border-bottom: 1px solid #999; padding-bottom: 2px; }
    .meta { color: #555; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
    th { background: #eee; }
    .totals td:first-child { font-weight: bold; width: 260px; }
</style>
</head>
<body>
    <h1>NDRRMC SitRep — {{ $disaster->name }}</h1>
    <p class="meta">
        {{ $disaster->type }} · {{ $disaster->zone }} · Signal {{ $disaster->signal }}<br>
        Generated {{ $generated_at->toDayDateTimeString() }}
    </p>

    <h2>Affected areas</h2>
    <table>
        <thead>
            <tr>
                <th>Area</th>
                <th>Population affected</th>
                <th>Severity</th>
                <th>Dead</th>
                <th>Injured</th>
                <th>Missing</th>
                <th>Families affected</th>
                <th>Houses (total)</th>
                <th>Houses (partial)</th>
                <th>Est. damage cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $zone)
            <tr>
                <td>{{ $zone->name }}</td>
                <td>{{ $zone->population_affected }}</td>
                <td>{{ $zone->severity_level }}</td>
                <td>{{ $zone->casualties_dead }}</td>
                <td>{{ $zone->casualties_injured }}</td>
                <td>{{ $zone->casualties_missing }}</td>
                <td>{{ $zone->families_affected }}</td>
                <td>{{ $zone->houses_totally_damaged }}</td>
                <td>{{ $zone->houses_partially_damaged }}</td>
                <td>
                    @if ($zone->estimated_damage_cost)
                        {{ $zone->damage_cost_currency }} {{ number_format($zone->estimated_damage_cost, 2) }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Totals</h2>
    <table class="totals">
        <tr><td>Dead</td><td>{{ $summary['casualties_dead'] }}</td></tr>
        <tr><td>Injured</td><td>{{ $summary['casualties_injured'] }}</td></tr>
        <tr><td>Missing</td><td>{{ $summary['casualties_missing'] }}</td></tr>
        <tr><td>Families affected</td><td>{{ $summary['families_affected'] }}</td></tr>
        <tr><td>Houses totally damaged</td><td>{{ $summary['houses_totally_damaged'] }}</td></tr>
        <tr><td>Houses partially damaged</td><td>{{ $summary['houses_partially_damaged'] }}</td></tr>
        <tr><td>Estimated damage cost</td><td>PHP {{ number_format($summary['estimated_damage_cost'], 2) }}</td></tr>
    </table>

    <h2>Evacuation centers</h2>
    @if ($evacuation_centers->isEmpty())
        <p>None recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Capacity</th>
                <th>Families served</th>
                <th>Individuals served</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($evacuation_centers as $center)
            <tr>
                <td>{{ $center->name }}</td>
                <td>{{ $center->address ?? '—' }}</td>
                <td>{{ $center->capacity ?? '—' }}</td>
                <td>{{ $center->families_served }}</td>
                <td>{{ $center->individuals_served }}</td>
                <td>{{ $center->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
