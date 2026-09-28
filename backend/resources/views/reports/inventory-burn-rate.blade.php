<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    h1 { font-size: 18px; margin-bottom: 2px; }
    .meta { color: #555; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
    th { background: #eee; }
</style>
</head>
<body>
    <h1>Inventory Burn-Rate &amp; Reorder Report</h1>
    <p class="meta">
        Generated {{ $generated_at->toDayDateTimeString() }}<br>
        Suggested reorder quantities are advisory only — Logistics staff still makes the final call, the same
        as the office's existing quarterly stock-review practice.
    </p>

    <table>
        <thead>
            <tr>
                <th>Depot</th>
                <th>Resource type</th>
                <th>Available</th>
                <th>Reserved</th>
                <th>Avg. quarterly consumption</th>
                <th>Suggested reorder</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row['depot'] }}</td>
                <td>{{ $row['resource_type'] }}</td>
                <td>{{ $row['quantity_available'] }}</td>
                <td>{{ $row['quantity_reserved'] }}</td>
                <td>{{ $row['avg_quarterly_consumption'] }}</td>
                <td>{{ $row['suggested_reorder'] }}</td>
                <td>{{ $row['note'] ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7">No resources on record yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
