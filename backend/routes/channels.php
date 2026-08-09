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

// Presence channel example — coordinators actively viewing the EOC dashboard.
Broadcast::channel('eoc-dashboard', function ($user) {
    return in_array($user->role, ['admin', 'coordinator'], true)
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});
