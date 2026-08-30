<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Disaster;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        return $this->sendResponse(
            $this->newQuery()->with(['zones', 'optimizationRuns' => fn ($q) => $q->latest()->limit(1)])->latest()->get()
        );
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->sendResponse(
            $this->resolveModel($id)->load(['zones', 'requests', 'optimizationRuns' => fn ($q) => $q->latest()->limit(1)])
        );
    }

    protected function storeRules(): array
    {
        return [
            // Client-supplied id lets the offline-sync outbox (Offline/sync.ts)
            // retry a POST after a partial network failure without risking a
            // duplicate incident — see store() below.
            'id' => ['sometimes', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'signal' => ['required', 'integer', 'min:1', 'max:5'],
            'zone' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'status' => ['nullable', 'in:ACTIVE,MONITORING,RESOLVED'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules());
        $id = $validated['id'] ?? (string) Str::uuid();
        unset($validated['id']);

        $validated = $this->beforeSave($validated);

        // See RequestController::store()'s comment — id isn't in $fillable,
        // so firstOrCreate's mass-assigned attributes would silently drop it.
        $model = Disaster::find($id);
        if (! $model) {
            $model = new Disaster($validated);
            $model->id = $id;
            $model->save();
            $this->afterCreate($model);
        }

        return $this->sendResponse($model, 'Record created successfully.', 201);
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
            'id' => ['sometimes', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'population_affected' => ['required', 'integer', 'min:0'],
            'severity_level' => ['required', 'in:High,Medium'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $id = $validated['id'] ?? (string) Str::uuid();
        unset($validated['id']);

        $zone = Zone::find($id);
        if (! $zone) {
            $zone = new Zone($validated + [
                'disaster_id' => $incident->id,
                'lat' => $validated['lat'] ?? $incident->lat,
                'lng' => $validated['lng'] ?? $incident->lng,
            ]);
            $zone->id = $id;
            $zone->save();
        }

        return $this->sendResponse($zone, 'Area added successfully.', 201);
    }

    /** POST /api/incidents/{incident}/areas/{area} — edits an area's population/severity and NDRRMC-standard fields. */
    public function updateArea(Request $request, Disaster $incident, Zone $area): JsonResponse
    {
        abort_unless($area->disaster_id === $incident->id, 404);

        $validated = $request->validate([
            'population_affected' => ['sometimes', 'integer', 'min:0'],
            'severity_level' => ['sometimes', 'in:High,Medium'],
            'casualties_dead' => ['sometimes', 'integer', 'min:0'],
            'casualties_injured' => ['sometimes', 'integer', 'min:0'],
            'casualties_missing' => ['sometimes', 'integer', 'min:0'],
            'families_affected' => ['sometimes', 'integer', 'min:0'],
            'houses_totally_damaged' => ['sometimes', 'integer', 'min:0'],
            'houses_partially_damaged' => ['sometimes', 'integer', 'min:0'],
            'estimated_damage_cost' => ['nullable', 'numeric', 'min:0'],
            'damage_cost_currency' => ['sometimes', 'string', 'max:3'],
        ]);

        $area->update($validated);

        return $this->sendResponse($area->fresh());
    }

    public function destroyArea(Disaster $incident, Zone $area): JsonResponse
    {
        abort_unless($area->disaster_id === $incident->id, 404);
        $area->delete();

        return response()->json(null, 204);
    }
}
