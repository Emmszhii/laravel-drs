<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel: any authenticated user can listen for replenishment run updates.
Broadcast::channel('replenishment', function ($user) {
    return $user !== null;
});
