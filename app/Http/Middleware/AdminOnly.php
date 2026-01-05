<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // sesuaikan kalau auth kamu custom
        if (!$user || ($user->role ?? null) !== 'admin') {
            abort(403, 'Admin only');
        }

        return $next($request);
    }
}
