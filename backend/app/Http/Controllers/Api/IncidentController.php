<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Disaster;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Incidents CRUD — pure REST endpoints for external callers and the
 * admin/developer Incidents page (Pages/Incidents/Index.tsx, CSR-fetched
 * via resources/js/api-service/incidents.ts).
 */
class IncidentController extends BaseController
{
    protected string $model = Disaster::class;

    protected array $searchable = ['name', 'zone'];

    protected array $filterable = ['signal', 'status'];

    protected array $exactFilters = ['signal', 'status'];

    protected array $sortable = ['id', 'name', 'signal', 'created_at'];

    protected string $sortBy = 'created_at';

    protected array $inUseRelations = ['zones', 'requests', 'optimizationRuns'];

    public function index(): JsonResponse
    {
        return $this->sendResponse($this->newQuery()->with('zones')->latest()->get());
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->sendResponse($this->resolveModel($id)->load('zones', 'requests'));
    }

    protected function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'signal' => ['required', 'integer', 'min:1', 'max:5'],
            'zone' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'status' => ['nullable', 'in:ACTIVE,MONITORING,RESOLVED'],
        ];
    }

    protected function updateRules(Model $model): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string'],
            'signal' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'zone' => ['sometimes', 'string'],
            'lat' => ['sometimes', 'numeric'],
            'lng' => ['sometimes', 'numeric'],
            'status' => ['sometimes', 'in:ACTIVE,MONITORING,RESOLVED'],
        ];
    }

    protected function beforeSave(array $validated, ?Model $model = null): array
    {
        if (! $model) {
            $validated['status'] = $validated['status'] ?? 'ACTIVE';
        }

        return $validated;
    }

    /** POST /api/incidents/{incident}/areas — adds a barangay-level affected area. */
    public function storeArea(Request $request, Disaster $incident): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'population_affected' => ['required', 'integer', 'min:0'],
            'severity_level' => ['required', 'in:High,Medium'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $zone = $incident->zones()->create($validated + ['lat' => $validated['lat'] ?? $incident->lat, 'lng' => $validated['lng'] ?? $incident->lng]);

        return $this->sendResponse($zone, 'Area added successfully.', 201);
    }

    public function destroyArea(Disaster $incident, Zone $area): JsonResponse
    {
        abort_unless($area->disaster_id === $incident->id, 404);
        $area->delete();

        return response()->json(null, 204);
    }
}
