<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Business;

class BusinessRegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:business');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('auth.business.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //Validate the request
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:businesses',
            'password' => 'required|string|min:6|confirmed',
        ]);

        //Create new JeliBusiness
        $business = Business::create([
            'name' => $request->name,
            'country' => $request->country,
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //Login new JeliBusiness
        Auth::guard('business')->login($business);

        //Redirect to Business Home Page
        return redirect()->route('business.home');
    }
}
