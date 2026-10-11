<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Backtest;
use App\Models\DataCheckFlag;
use App\Models\QuarterBudget;
use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
use App\Models\User;
use App\Models\VedPolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Quarterly replenishment dashboard, built on the three situation-awareness
 * levels: current use and stock, the recommendation with a reason per item,
 * and the budget curve with the "next P10,000 goes to" view. Three subtabs:
 * the recommendation (Pages/Replenishment/Index.tsx), the backtest, and the
 * inputs (VED policy and history import). The report has its own page
 * (ReportController). Writes go through Api\ReplenishmentController.
 */
class ReplenishmentController extends Controller
{
    public function index(Request $request): Response
    {
        $runs = ReplenishmentRun::latest()->limit(20)
            ->get(['id', 'year', 'quarter', 'budget', 'status', 'rule', 'created_at', 'decided_at']);
        $run = $request->query('run')
            ? ReplenishmentRun::find($request->query('run'))
            : ReplenishmentRun::latest()->first();

        return Inertia::render('Replenishment/Index', [
            'runs' => $runs,
            'run' => $run?->makeHidden('inputs'),
            'items' => ResourceType::orderBy('name')->get(['id', 'name', 'unit', 'ved_class', 'unit_cost']),
            'flags' => DataCheckFlag::with('resourceType:id,name')->where('status', 'open')->get(),
            'policies' => $this->policies(),
            'defaults' => $this->defaults(),
            'can' => $this->can($request->user()),
        ]);
    }

    public function backtest(Request $request): Response
    {
        return Inertia::render('Replenishment/Backtest', [
            'backtest' => Backtest::latest()->first()?->makeHidden('inputs'),
            'defaults' => $this->defaults(),
            'can' => $this->can($request->user()),
        ]);
    }

    public function inputs(Request $request): Response
    {
        return Inertia::render('Replenishment/Inputs', [
            'policies' => $this->policies(),
            'flags' => DataCheckFlag::where('status', 'open')->count(),
            'can' => $this->can($request->user()),
        ]);
    }

    private function policies()
    {
        return VedPolicy::orderByRaw("CASE ved_class WHEN 'V' THEN 1 WHEN 'E' THEN 2 ELSE 3 END")->get();
    }

    /** The quarter being planned is the next one; its budget, if the Admin entered it. */
    private function defaults(): array
    {
        $next = now()->addQuarter();

        return [
            'year' => $next->year,
            'quarter' => $next->quarter,
            'budget' => QuarterBudget::where('year', $next->year)->where('quarter', $next->quarter)->value('budget_amount'),
        ];
    }

    private function can(User $user): array
    {
        return [
            'run' => (bool) $user->is_system_admin,
            'encode' => $user->is_system_admin || $user->role === 'logistics',
            'export' => $user->is_system_admin || in_array($user->role, ['logistics', 'research_planning'], true),
        ];
    }
}
