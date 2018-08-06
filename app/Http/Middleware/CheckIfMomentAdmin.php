<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfMomentAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        auth('api')->user()->moments()->find($request->route('moment')->id)

        return $next($request);
    }
}
