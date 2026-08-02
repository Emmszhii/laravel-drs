<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();
        $target = match (true) {
            $user->hasRole('developer') => route('developer.dashboard'),
            $user->hasRole('admin') => route('admin.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($target);
    }
}
