<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Log a deactivated user out on their very next request, wherever they go.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only an explicit "inactive" counts. A user object created in memory
        // (as the tests do) has no value yet, and a user loaded from the
        // database always has one.
        if ($user && $user->is_active !== null && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact a fellowship administrator.',
            ]);
        }

        return $next($request);
    }
}