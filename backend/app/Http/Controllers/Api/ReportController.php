<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Disaster;
use App\Models\Report;
use App\Services\Reports\InventoryBurnRateCsvExporter;
use App\Services\Reports\InventoryBurnRateReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports. Most types have no real pipeline yet — "download" streams a
 * synthesized text summary. "Inventory Burn-Rate Report" is the exception:
 * it's backed by InventoryBurnRateReportBuilder and rendered as a real PDF
 * (dompdf) or CSV.
 */
class ReportController extends BaseController
{
    protected string $model = Report::class;

    protected array $searchable = ['name', 'disaster.name'];

    protected array $filterable = ['type'];

    protected array $exactFilters = ['type'];

    protected array $sortable = ['id', 'generated_at'];

    protected string $sortBy = 'generated_at';

    private const TYPES = ['After-action summary', 'Resource utilization', 'Response timeline', 'Inventory Burn-Rate Report'];

    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with('disaster')->latest('generated_at')->get());
    }

    protected function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Report::query()->with('disaster');
    }

    /** "Inventory Burn-Rate Report" spans depots/quarters, not one incident, so disaster_id is conditionally required. */
    private const DISASTER_SCOPED_TYPES = ['After-action summary', 'Resource utilization', 'Response timeline'];

    protected function storeRules(): array
    {
        return [
            'disaster_id' => [
                Rule::requiredIf(fn () => in_array(request('type'), self::DISASTER_SCOPED_TYPES, true)),
                'nullable', 'uuid', 'exists:app_disasters,id',
            ],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (! empty($validated['disaster_id'])) {
            Disaster::findOrFail($validated['disaster_id']);
        }

        $validated['name'] = $validated['type'];
        $validated['generated_at'] = now();

        return $validated;
    }

    public function download(Request $request, Report $report): StreamedResponse|Response
    {
        $report->loadMissing('disaster');

        if ($report->type === 'Inventory Burn-Rate Report') {
            return $this->downloadInventoryBurnRateReport($request, $report);
        }

        $lines = [
            $report->type,
            'Incident: '.($report->disaster->name ?? 'Unknown'),
            'Generated: '.$report->generated_at->toDayDateTimeString(),
            '',
            'This is a placeholder report — no real reporting pipeline is wired up yet.',
        ];

        $content = implode("\n", $lines);
        $filename = str($report->type)->slug().'-'.$report->id.'.txt';

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'text/plain']
        );
    }

    private function downloadInventoryBurnRateReport(Request $request, Report $report): StreamedResponse|Response
    {
        $data = app(InventoryBurnRateReportBuilder::class)->build();
        $format = $request->query('format', 'pdf');
        $slug = str($report->generated_at->toDateString())->slug();

        if ($format === 'csv') {
            return response()->streamDownload(
                fn () => app(InventoryBurnRateCsvExporter::class)->stream($data),
                "inventory-burn-rate-{$slug}.csv",
                ['Content-Type' => 'text/csv']
            );
        }

        return Pdf::loadView('reports.inventory-burn-rate', $data)
            ->download("inventory-burn-rate-{$slug}.pdf");
    }
}
