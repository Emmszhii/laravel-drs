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
    td.num { text-align: right; }
    .fingerprint { font-family: DejaVu Sans Mono, monospace; font-size: 9px; word-break: break-all; }
    .bad { color: #b00020; font-weight: bold; }
</style>
</head>
<body>
@foreach ($reports as $report)
@php extract($report); @endphp
<div @if (! $loop->last) style="page-break-after: always" @endif>
    <h1>Quarterly Burn-Rate &amp; Reorder Report — {{ $year }} Q{{ $quarter }}</h1>
    <p class="meta">
        CDRRMO San Pablo City · Generated {{ $generated_at->toDayDateTimeString() }}<br>
        @if ($plan)
            Purchase plan approved by the Head of Office{{ $plan['approved_at'] ? ' on ' . $plan['approved_at']->toFormattedDateString() : '' }}:
            budget ₱{{ number_format($plan['budget'], 2) }}, spends ₱{{ number_format($plan['spent'] ?? 0, 2) }}.
            Recommendations only; purchases go through GSO bidding under RA 12009.
        @else
            No purchase plan has been approved for this quarter yet.
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>On hand</th>
                <th>Reserved</th>
                <th>Burn rate / quarter</th>
                <th>Quarters of cover</th>
                <th>Target</th>
                <th>Reorder</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row['resource_type'] }} ({{ $row['unit'] }})</td>
                <td class="num">{{ $row['on_hand'] }}</td>
                <td class="num">{{ $row['reserved'] }}</td>
                <td class="num">{{ $row['burn_rate'] }}</td>
                <td class="num">{{ $row['quarters_of_cover'] ?? '—' }}</td>
                <td class="num">{{ $row['target'] ?? '—' }}</td>
                <td class="num">{{ $row['reorder'] ?? '—' }}</td>
                <td>{{ $row['reason'] ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8">No items on record yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p>
        Stock records fingerprint (latest entry, SHA-256):<br>
        <span class="fingerprint">{{ $fingerprint }}</span><br>
        @if ($records_ok)
            "Check records" passed when this report was generated.
        @else
            <span class="bad">"Check records" FAILED when this report was generated.</span>
        @endif
        Keep this copy: a chain rebuilt later would no longer match the fingerprint printed here.
    </p>
</div>
@endforeach
</body>
</html>
