<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Mail\CustomerWelcome;
use App\Customer;
use App\Notifications\SendOTPNotification;

class APICustomerController extends Controller
{
    protected $OTP;

    protected function generateOTP() {
        $this->OTP = mt_rand(100000, 999999);

        return $this->OTP;
    }

    /**
     * Verify OTP sent by customer via SMS
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyOTP(Request $request, Customer $customer) {

        $this->validate(request(), [
            'otp' => 'required|integer|between:100000,999999',
        ]);

        $otp = $request->otp;

        if(! $otp == $customer->otp ) {
            return response()->json(['success' => false, 'error' => 'Wrong pin entered']);
        }

        //Change status to verified:1
        $customer->status = 1;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Customer has been verified'
        ], 200);
        //After PIN is verified login request is made
        //So check if PIN has been verified in login request
    }

    /**
     * Generate new OTP and send to customer via SMS
     *
     * @return \Illuminate\Http\Response
     */
    public function requestNewOTP(Customer $customer) {

        $customer->otp = $this->genereateOTP();
        $customer->save();

        //Send email with OTP to customer
        if($customer->email){
            \Mail::to($customer)->send(new CustomerWelcome($customer));
        }

        //Send new one time pin via SMS
        $customer->notify(new SendOTPNotification($customer->otp));

        return response()->json([
            'success' => true,
            'message' => 'New verification code has been sent'
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Customer::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate Request with following rules
        $this->validate(request(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone' => 'required|string|max:20|unique:customers',
            'email' => 'string|email|max:255|unique:customers',
            'password' => 'required|confirmed',
        ]);

        //Generate One Time PIN
        $this->generateOTP();

        //Create customer entry in database
        $customer = Customer::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' =>$request['phone'],
            'email' => $request['email'],
            'jelion' => $request['jelion'],
            'password' => bcrypt($request['password']),
            'otp' => $this->OTP,
        ]);

        //Send email with OTP to customer
        if($request->email){
            \Mail::to($customer)->send(new CustomerWelcome($customer));
        }

        //Send OTP to Customers phone via SMS
        $customer->notify(new SendOTPNotification($customer->otp));

        return response()->json([
            'success' => true,
            'message' => 'Verification code has been sent'
        ], 201, //HTTP status code 201: Object created
        [ 
            'Location' => '/customers/'. $customer->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        return $customer;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->update($request->all);

        return response()->json($customer, 200);
        //HTTP status code 200: OK
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 15
        ]);
    }
}
