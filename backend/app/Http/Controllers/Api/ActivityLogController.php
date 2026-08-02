<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the header's dispatch-log ticker and notification bell — both read
 * the same `app_loggers` audit trail (see ActivityLogServiceProvider for the
 * audited-model allowlist), just rendered differently on the frontend.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->integer('limit', 30), 100);

        $entries = AppLogger::query()
            ->whereNotIn('action', ['visit', 'error'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AppLogger $log) => [
                'id' => $log->id,
                'ts' => $log->created_at?->format('H:i:s'),
                'tag' => strtoupper($log->action),
                'message' => $log->description ?? ucfirst($log->action),
            ]);

        return response()->json(['data' => $entries]);
    }
}
