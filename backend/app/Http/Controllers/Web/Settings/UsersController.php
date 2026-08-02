<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Users list (Inertia page: Pages/Settings/Users.tsx), admin/developer only.
 * Data is fetched client-side via resources/js/api-service/users.ts.
 */
class UsersController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Users');
    }
}
