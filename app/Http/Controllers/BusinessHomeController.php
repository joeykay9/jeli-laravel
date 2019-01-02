<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD

class BusinessHomeController extends Controller
{
	public function __construct()
	{
		$this->middleware('web');
	}

    public function index()
    {
    	return view('business.home');
    }
}
=======
use App\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class BusinessHomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:business');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('business.home');
    }

    public function registerVendor(Request $request){
        //Validate request
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:vendors',
            'password' => 'required|string|min:6|confirmed',
        ]);

        //Create new JeliVendor
        $auth = Auth::guard('business');

        $auth->user()->createVendor(
            new Vendor([
                'name' => request('name'),
                'email' => request('email'),
                'password' => Hash::make(request('password')),
            ])
        );

        return redirect()->back();
    }

    public function destroyVendor(Vendor $vendor){
        //Delete a particular JeliVendor Account
    }
}
>>>>>>> parent of 3a83732... plenty jeli business steezes
