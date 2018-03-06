<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Vendor;

class VendorLoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:vendor')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.vendor.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'business_email' => 'required|email|string',
        	'email' => 'required|email|string',
        	'password' => 'required|string',
        ]);

        //Get requested vendor details
        $vendor = Vendor::where('email', '=', $request->email)->first();
        
        if(! $vendor){ //Check if vendor is not valid
            return redirect()->back()->withErrors([
                'message' => 'Please check your credentials and try again.'
            ]);
        }

        //Check if vendor belongs to business
        $business = $vendor->business;

        if(! ($business->email == $request->business_email)) {

            return redirect()->back()->withErrors([
                'message' => 'Please check your credentials and try again.'
            ]);
        }

        //Login Vendor
        Auth::guard('vendor')->login($vendor);

        return redirect()->route('vendor.home');
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::guard('vendor')->logout();

        return redirect()->route('vendor.login');
    }
}
