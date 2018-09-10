<?php

namespace App\Http\Middleware;

use Closure;
use App\Customer;

class CheckIfAccountExists
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
        if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
            $customer = Customer::where('email', $request->username)->first();
        } else {
            $customer = Customer::where('phone', $request->username)->first();
        }

        if (! $customer) {
            return response()->json([
                    'success' => false,
                    'errors' => ['Incorrect username or password. Please check your credentials.']
                ], 401); //401: Unauthorized
        }

        return $next($request);
    }
}
