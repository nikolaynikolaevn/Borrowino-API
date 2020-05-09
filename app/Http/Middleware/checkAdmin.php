<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use const http\Client\Curl\AUTH_ANY;

class checkAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $auth;

    public function handle($request, Closure $next)
    {

            if ($request->user() && $request->user()-> isAdmin)
        {
            return new Response(view('unauthorized'));
        }
        return $next($request);
    }


}
