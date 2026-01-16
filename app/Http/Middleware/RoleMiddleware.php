<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles){
        if (!Auth::check()) {

            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->guest(route('auth.login'));
        }

        if (!in_array(Auth::user()->role->value, $roles)) {

            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'You do not have the required role.',
                ], 403);
            }

            abort(403);
        }

        return $next($request);
    }

}
