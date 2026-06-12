<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminCheck
{
    public function handle(Request $request, Closure $next)
    {
        $user = session('user');

        if (!$user || !in_array($user->role, [1, 2])) {
            return redirect('/dashboard')->with('error', 'Access denied. Admins only.');
        }

        return $next($request);
    }
}
