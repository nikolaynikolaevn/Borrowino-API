<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    protected $auth;

    public function handle($request, Closure $next)
    {
        if ($request->user() && $request->user()->isAdmin) {
            return response()->json(['Message' => 'Unauthorized'],401);
        }
        return $next($request);
    }
}
