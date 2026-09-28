<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel: any authenticated user can listen for incident alerts.
Broadcast::channel('disasters', function ($user) {
    return $user !== null;
});

// Public channel: any authenticated user can listen for live dispatch position updates.
Broadcast::channel('dispatches', function ($user) {
    return $user !== null;
});

// Presence channel example — Incident Management/Operations/Admin & Training
// staff actively viewing the EOC dashboard.
Broadcast::channel('eoc-dashboard', function ($user) {
    return ($user->is_system_admin || in_array($user->role, ['incident_management', 'operations', 'admin_training'], true))
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});

// Public channel: any authenticated user can listen for replenishment run updates.
Broadcast::channel('replenishment', function ($user) {
    return $user !== null;
});
