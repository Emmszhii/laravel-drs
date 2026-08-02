<?php

namespace App\Providers;

use App\Models\Allocation;
use App\Models\Depot;
use App\Models\Disaster;
use App\Models\OptimizationRun;
use App\Models\Report;
use App\Models\User;
use App\Observers\AppLoggerObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

/**
 * Registers the audit observer against the domain models whose lifecycle should
 * be logged. Kept as an explicit allowlist (rather than a blanket global model
 * event) so framework/pivot/audit tables are never logged, and so the full set
 * of audited models is visible in one place. Add a model here to audit it.
 *
 * Disaster/OptimizationRun/Report/Allocation/Depot are audited so the header's
 * dispatch-log ticker and notification bell (both backed by `app_loggers`) have
 * real disaster-response activity to show, not just user-management changes.
 */
class ActivityLogServiceProvider extends ServiceProvider
{
    /** @var list<class-string<\Illuminate\Database\Eloquent\Model>> */
    private array $auditedModels = [
        User::class,
        Role::class,
        Disaster::class,
        OptimizationRun::class,
        Report::class,
        Allocation::class,
        Depot::class,
    ];

    public function boot(): void
    {
        foreach ($this->auditedModels as $model) {
            $model::observe(AppLoggerObserver::class);
        }
    }
}
