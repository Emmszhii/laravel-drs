<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Request kits (e.g. "Medical backpack #1"): saved by Logistics, loaded into a new
 * ICS 213-RR on the requests page. Saving under an existing name replaces its lines.
 */
class KitController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $kit = DB::transaction(function () use ($data) {
            $kit = Kit::firstOrCreate(['name' => $data['name']]);
            $kit->lines()->delete();
            $kit->lines()->createMany($data['lines']);

            return $kit;
        });

        return response()->json($kit->load('lines'), 201);
    }

    public function destroy(Kit $kit): Response
    {
        $kit->delete();

        return response()->noContent();
    }
}
