<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // 1) Belum login
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Unauthorized',
                ], 401);
            }
            return redirect('/login.html');
        }

        // 2) Sudah login tapi bukan admin
        $role = strtolower((string)($user->role ?? ''));
        if ($role !== 'admin') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Forbidden (admin only)',
                ], 403);
            }
            return redirect('/index.php');
        }

        return $next($request);
    }
}
