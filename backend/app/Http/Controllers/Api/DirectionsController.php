<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Directions\DirectionsRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Point-to-point driving directions (route geometry + turn-by-turn steps)
 * for the map's navigation panel. Routes through DirectionsRouter, which
 * transparently picks OSRM (free) or an admin-configured premium provider.
 */
class DirectionsController extends Controller
{
    public function index(Request $request, DirectionsRouter $router): JsonResponse
    {
        $validated = $request->validate([
            'from_lat' => ['required', 'numeric', 'between:-90,90'],
            'from_lng' => ['required', 'numeric', 'between:-180,180'],
            'to_lat' => ['required', 'numeric', 'between:-90,90'],
            'to_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $router->getRoute(
            (float) $validated['from_lat'],
            (float) $validated['from_lng'],
            (float) $validated['to_lat'],
            (float) $validated['to_lng'],
        );

        return response()->json($result);
    }
}
