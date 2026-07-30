<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pure REST endpoints for external callers (mobile clients, third-party
 * integrations, or the solver-service posting back optimization results).
 * Web/Inertia pages should use Http/Controllers/Web instead.
 */
class IncidentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Disaster::with('zones')->latest()->get()
        );
    }

    public function show(Disaster $incident): JsonResponse
    {
        return response()->json($incident->load('zones', 'requests'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'signal' => ['required', 'integer', 'min:1', 'max:5'],
            'zone' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $incident = Disaster::create($validated + ['status' => 'ACTIVE']);

        return response()->json($incident, 201);
    }
}
