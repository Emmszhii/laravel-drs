<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only users list (Inertia page: Pages/Settings/Users.tsx), admin/developer only.
 */
class UsersController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Users', [
            'users' => User::all(['id', 'name', 'email', 'role']),
        ]);
    }
}
