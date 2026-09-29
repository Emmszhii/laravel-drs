<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\IcsRequest;
use App\Services\Requests\RequestWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * ICS 213-RR requests and the borrowed-equipment logbook (system-spec.md §6).
 * Requesters (Logistics, ERT, Operations, Incident Management) file; only the
 * Admin approves or rejects; Logistics releases, closes, and records what
 * happens to borrowed items. The Admin can do all of it, including approving
 * a request they filed.
 */
class IcsRequestController extends Controller
{
    public const PURPOSES = ['team_kit', 'relief', 'office_use'];

    public function __construct(private readonly RequestWorkflow $workflow)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(IcsRequest::with('lines.resourceType:id,name,unit,is_consumable', 'requester:id,first_name,last_name')->latest()->limit(100)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'requesting_division' => ['required', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'purpose' => ['required', Rule::in(self::PURPOSES)],
            'event_tag' => ['nullable', 'string', 'max:100'],
            'submit' => ['boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'lines.*.quantity_requested' => ['required', 'integer', 'min:1'],
        ]);

        $ics = IcsRequest::create([
            'requesting_division' => $data['requesting_division'],
            'requested_by' => $request->user()->id,
            'date' => $data['date'] ?? now()->toDateString(),
            'purpose' => $data['purpose'],
            'event_tag' => $data['event_tag'] ?? null,
            'status' => 'draft',
        ]);
        $ics->lines()->createMany(array_map(fn ($l) => ['resource_type_id' => $l['resource_type_id'], 'quantity_requested' => $l['quantity_requested']], $data['lines']));
        if ($data['submit'] ?? true) {
            $this->workflow->submit($ics, $request->user()->id);
        }

        return response()->json($ics->load('lines'), 201);
    }

    public function submit(Request $request, IcsRequest $ics): JsonResponse
    {
        $this->ownOrAdmin($request, $ics);
        $this->workflow->submit($ics, $request->user()->id);

        return response()->json($ics);
    }

    public function approve(Request $request, IcsRequest $ics): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.quantity_approved' => ['required', 'integer', 'min:0'],
            'lines.*.depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
        ]);

        $flagged = $this->workflow->approve($ics, $data['lines'], $request->user()->id);

        return response()->json(['request' => $ics->load('lines'), 'lines_exceeding_stock' => $flagged]);
    }

    public function reject(Request $request, IcsRequest $ics): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $this->workflow->reject($ics, $data['reason'], $request->user()->id);

        return response()->json($ics);
    }

    public function release(Request $request, IcsRequest $ics): JsonResponse
    {
        $data = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['integer', 'min:0'],
            'borrower_name' => ['nullable', 'string', 'max:150'],
            'borrower_contact' => ['nullable', 'string', 'max:100'],
            'borrower_division' => ['nullable', 'string', 'max:100'],
            'property_numbers' => ['nullable', 'array'],
        ]);
        $this->workflow->release($ics, $data['quantities'], $data, $request->user()->id);

        return response()->json($ics->fresh('lines'));
    }

    public function close(Request $request, IcsRequest $ics): JsonResponse
    {
        $this->workflow->close($ics, $request->user()->id, $request->input('note'));

        return response()->json($ics);
    }

    public function cancel(Request $request, IcsRequest $ics): JsonResponse
    {
        $isAdmin = (bool) $request->user()->is_system_admin;
        if (! $isAdmin) {
            $this->ownOrAdmin($request, $ics);
        }
        $this->workflow->cancel($ics, $request->user()->id, $isAdmin);

        return response()->json($ics);
    }

    /** The logbook. Borrower names and contacts go only to the Admin and Logistics. */
    public function borrows(Request $request): JsonResponse
    {
        $borrows = Borrow::with('resourceType:id,name')->latest('issued_at')->limit(200)->get();
        if ($this->seesPersonalData($request)) {
            $borrows->each->makeVisible(['borrower_name', 'borrower_contact']);
        }

        return response()->json($borrows);
    }

    public function borrowStatus(Request $request, Borrow $borrow): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['returned', 'for_repair', 'condemned', 'surrendered'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->workflow->changeBorrowStatus($borrow, $data['status'], $request->user()->id, $data['note'] ?? null);

        return response()->json($borrow->makeVisible(['borrower_name', 'borrower_contact']));
    }

    public static function seesPersonalData(Request $request): bool
    {
        return $request->user()->is_system_admin || $request->user()->role === 'logistics';
    }

    private function ownOrAdmin(Request $request, IcsRequest $ics): void
    {
        abort_unless($ics->requested_by === $request->user()->id || $request->user()->is_system_admin, 403, 'Only the requester can do this.');
    }
}
