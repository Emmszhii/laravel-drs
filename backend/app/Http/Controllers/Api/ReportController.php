<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Disaster;
use App\Models\Report;
use App\Services\Reports\NdrrmcCsvExporter;
use App\Services\Reports\NdrrmcReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports. Most types have no real pipeline yet — "download" streams a
 * synthesized text summary. "NDRRMC SitRep" is the exception: it's backed
 * by NdrrmcReportBuilder and rendered as a real PDF (dompdf) or CSV.
 */
class ReportController extends BaseController
{
    protected string $model = Report::class;

    protected array $searchable = ['name', 'disaster.name'];

    protected array $filterable = ['type'];

    protected array $exactFilters = ['type'];

    protected array $sortable = ['id', 'generated_at'];

    protected string $sortBy = 'generated_at';

    private const TYPES = ['After-action summary', 'Resource utilization', 'Response timeline', 'NDRRMC SitRep'];

    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with('disaster')->latest('generated_at')->get());
    }

    protected function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Report::query()->with('disaster');
    }

    protected function storeRules(): array
    {
        return [
            'disaster_id' => ['required', 'uuid', 'exists:app_disasters,id'],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        $disaster = Disaster::findOrFail($validated['disaster_id']);
        $validated['name'] = $validated['type'];
        $validated['generated_at'] = now();

        return $validated;
    }

    public function download(Request $request, Report $report): StreamedResponse|Response
    {
        $report->loadMissing('disaster');

        if ($report->type === 'NDRRMC SitRep') {
            return $this->downloadNdrrmcSitrep($request, $report);
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

    private function downloadNdrrmcSitrep(Request $request, Report $report): StreamedResponse|Response
    {
        $disaster = $report->disaster ?? Disaster::findOrFail($report->disaster_id);
        $data = app(NdrrmcReportBuilder::class)->build($disaster);
        $format = $request->query('format', 'pdf');
        $slug = str($disaster->name)->slug();

        if ($format === 'csv') {
            return response()->streamDownload(
                fn () => app(NdrrmcCsvExporter::class)->stream($data),
                "ndrrmc-sitrep-{$slug}.csv",
                ['Content-Type' => 'text/csv']
            );
        }

        return Pdf::loadView('reports.ndrrmc-sitrep', $data)
            ->download("ndrrmc-sitrep-{$slug}.pdf");
    }
}
