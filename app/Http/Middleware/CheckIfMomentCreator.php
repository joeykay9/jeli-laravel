<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfMomentCreator
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
        if(! auth('api')->user()->createdMoments()
                ->find($request->route('moment')->id)) {
            return response()->json([
                'success' => false,
                'errors' => ['Unauthorized']
            ], 401);
        }

        return $next($request);
    }
}
