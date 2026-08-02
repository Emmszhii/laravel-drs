<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Disaster;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports — no real PDF pipeline yet; "generate" creates a row and
 * "download" streams a synthesized text summary from the linked incident.
 */
class ReportController extends BaseController
{
    protected string $model = Report::class;

    protected array $searchable = ['name', 'disaster.name'];

    protected array $filterable = ['type'];

    protected array $exactFilters = ['type'];

    protected array $sortable = ['id', 'generated_at'];

    protected string $sortBy = 'generated_at';

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
            'type' => ['required', 'in:After-action summary,Resource utilization,Response timeline'],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        $disaster = Disaster::findOrFail($validated['disaster_id']);
        $validated['name'] = $validated['type'];
        $validated['generated_at'] = now();

        return $validated;
    }

    public function download(Report $report): StreamedResponse
    {
        $report->loadMissing('disaster');

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
}
