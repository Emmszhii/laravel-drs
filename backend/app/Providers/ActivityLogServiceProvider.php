<?php

namespace App\Providers;

use App\Models\Depot;
use App\Models\IcsRequest;
use App\Models\ReplenishmentRun;
use App\Models\ResourceType;
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
 * ICS 213-RR requests are audited so the notification bell (backed by
 * `app_loggers`) shows request activity. Replenishment runs are not: each
 * holds its full input snapshot, and run decisions are already on the run.
 */
class ActivityLogServiceProvider extends ServiceProvider
{
    /** @var list<class-string<\Illuminate\Database\Eloquent\Model>> */
    private array $auditedModels = [
        User::class,
        Role::class,
        Depot::class,
        ResourceType::class,
        IcsRequest::class,
    ];

    public function boot(): void
    {
        foreach ($this->auditedModels as $model) {
            $model::observe(AppLoggerObserver::class);
        }
    }
}
