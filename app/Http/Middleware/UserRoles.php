<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UserRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
        * @param  \Illuminate\Http\Request  $request
        * @param  \Closure  $next
        * @return mixed
        */

        if (Auth::check()) {
            $currentUser = Auth::user();

            // Get the user_id and role_id stored in session
            $sessionUserId = Session::get('user_id');
            $sessionRoleId = Session::get('role_id');

            // If session data exists and it doesn't match the current user data, log out
            if ($sessionUserId !== null && $sessionUserId !== $currentUser->id) {
                Auth::logout();
                Session::flush();
                return redirect('/')->withErrors('Another user logged in. Please log in again.');
            }

            if ($sessionRoleId !== null && $sessionRoleId !== $currentUser->role_id) {
                Auth::logout();
                Session::flush();
                return redirect('/')->withErrors('Oops! Invalidsession. Please log in again.');
            }

            // Store the current user_id and role_id in session if they are not set
            Session::put('user_id', $currentUser->id);
            Session::put('role_id', $currentUser->role_id);
        }

        return $next($request);
    }
}
