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
 * Requesters (Logistics, and Operations and Warning with its teams) file; only the
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
        return response()->json(IcsRequest::with('lines.resourceType:id,name,unit,category,is_consumable', 'requester:id,first_name,last_name')->latest()->limit(100)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'control_no' => ['nullable', 'string', 'max:30', 'unique:app_ics_requests,control_no'], // the number printed on the paper form
            'requesting_division' => ['required', 'string', 'max:100'], // division and team, e.g. "Operations / Team Delta"
            'requester_name' => ['nullable', 'string', 'max:150'], // form item 1; defaults to the logged-in user
            'requester_position' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subDays(31)->toDateString()], // backdating would plant demand in a past quarter
            'time' => ['nullable', 'date_format:H:i'],
            'purpose' => ['required', Rule::in(self::PURPOSES)],
            'event_tag' => ['nullable', 'string', 'max:100'],
            'submit' => ['boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'lines.*.quantity_requested' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $user = $request->user();
        $ics = IcsRequest::create([
            'control_no' => $data['control_no'] ?? null,
            'requesting_division' => $data['requesting_division'],
            'requested_by' => $user->id,
            'requester_name' => $data['requester_name'] ?? $user->name,
            'requester_position' => $data['requester_position'] ?? $user->position,
            'date' => $data['date'] ?? now()->toDateString(),
            'time' => $data['time'] ?? now()->format('H:i'),
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
            'lines.*.reason' => ['nullable', 'string', 'max:500'], // required by the workflow when the line is cut
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
            'borrower_division' => ['nullable', 'string', 'max:100'], // another office, when the borrower isn't from the requesting division
            'location' => ['nullable', 'string', 'max:150'],
            'property_numbers' => ['nullable', 'array'],
        ]);
        $this->workflow->release($ics, $data['quantities'], $data, $request->user()->id);

        return response()->json($ics->fresh('lines'));
    }

    /** Form item 11: Logistics' reply. One editable text, as on the paper form. */
    public function remarks(Request $request, IcsRequest $ics): JsonResponse
    {
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:2000']]);
        $ics->update(['remarks' => $data['remarks'] ?? null]);

        return response()->json($ics);
    }

    /** A walk-in loan with no ICS 213-RR behind it, to any office: the logbook's usual case. */
    public function lend(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resource_type_id' => ['required', 'uuid', 'exists:app_resource_types,id'],
            'depot_id' => ['required', 'uuid', 'exists:app_depots,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'borrower_division' => ['required', 'string', 'max:100'],
            'borrower_name' => ['required', 'string', 'max:150'],
            'borrower_contact' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'property_numbers' => ['nullable', 'array'],
            'property_numbers.*' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json($this->workflow->lend($data, $request->user()->id), 201);
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
