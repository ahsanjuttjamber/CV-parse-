<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // agar user logged in nahi hai
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        // agar role match nahi karta
        if (Auth::user()->role !== $role) {
            // admin page hit kiya lekin role user hai
            if ($role === 'admin') {
                return redirect()->route('user.dashboard');
            }
            // user page hit kiya lekin role admin hai
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
