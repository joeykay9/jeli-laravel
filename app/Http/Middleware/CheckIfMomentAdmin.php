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
        if(! auth('api')->user()->moments()
                ->find($request->route('moment')->id)
                ->pivot
                ->is_grp_admin) {
            return response()->json([
                'success' => false,
                'errors' => ['Unauthorized']
            ], 401);
        }

        return $next($request);
    }
}
