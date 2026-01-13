<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {

        if(!Auth::check()){
            return $this->unauthorizedResponse(
                $request,
                'Unauthorized',
                401
            );
        }

        if(!in_array(Auth::user()->role->value, $roles)){
            return $this->unauthorizedResponse(
                $request,
                'You do not have the required role.',
                403
            );
        }

        return $next($request);
    }

    protected function unauthorizedResponse(Request $request, string $message, int $status)
    {

        if ($request->is('api/*')) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        abort($status, $message);
    }
}
