<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        $user =  $request->user();

        if(!in_array($user->role, $role)){
            abort(403);
        }

        return $next($request);
    }
}
