<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/');  // Redirect to login if user is not authenticated
        }

        $user = Auth::user();

        // Check if the user's role matches any of the allowed roles
        if (!in_array($user->role_id, $roles)) {
            abort(403, 'Unauthorized access');  // Block access if user role doesn't match
        }

        return $next($request);
    }
}
