<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Mail\CustomerWelcome;
use App\Customer;
use App\Notifications\SendOTPNotification;
use Propaganistas\LaravelPhone\PhoneNumber;

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

        $credentials = $request->only('otp');

        $rules = [
            'otp' => 'required|integer|between:100000,999999',
        ];

        $messages = [
            'between' => 'The OTP must be six digits'
        ];

        $validator = Validator::make($credentials, $rules, $messages);

        if(! $customer->verified) {
            $otp = $request->otp;

            if($otp != $customer->otp) {
                return response()->json([
                    'success' => false, 
                    'errors' => ['Wrong pin entered']
                ], 401);
            }

            //Change status to verified:1
            $customer->verified = true;
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Customer has been verified'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'errors' => ['Customer has already been verified']
        ], 401);
        //After PIN is verified login request is made
        //So check if PIN has been verified in login request
    }

    /**
     * Generate new OTP and send to customer via SMS
     *
     * @return \Illuminate\Http\Response
     */
    public function requestNewOTP(Customer $customer) {

        $customer->otp = $this->generateOTP();
        $customer->save();

        //Send email with OTP to customer
        if($customer->email){
            \Mail::to($customer)->send(new CustomerWelcome($customer));
        }

        //Send new one time pin via SMS
        $customer->notify(new SendOTPNotification($customer->otp));

        return response()->json([
            'success' => true,
            'message' => 'New verification code has been sent',
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
        $credentials = $request->all();
            if($request->filled('phone')) {
                $credentials['phone'] = (string) PhoneNumber::make($request->phone, 'GH');
            }

        $rules = [
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'phone' => 'bail|phone:AUTO,GH|required|string|max:15|unique:customers', //should be required from the app
            'email' => 'bail|nullable|string|email|max:50|unique:customers', //Email already exists
            'password' => 'required|confirmed', //should be required from the app
        ];

        $messages = [
            'required' => 'The :attribute field is required.',
            'phone.max' => 'Please provide a valid :attribute number.',
            'email' => 'Please provide a valid email address.',
        ];

        $validator = Validator::make($credentials, $rules, $messages);
        
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //Generate One Time PIN
        $this->generateOTP();

        //Create customer entry in database
        $customer = Customer::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' =>$credentials['phone'],
            'email' => $request['email'],
            'jelion' => $request['jelion'],
            'password' => bcrypt($request['password']),
            'otp' => $this->OTP,
        ]);

        //Send email with OTP to customer
        if($request->fiiled('email')) {
            \Mail::to($customer)->send(new CustomerWelcome($customer));
        }

        //Send OTP to Customers phone via SMS
        $customer->notify(new SendOTPNotification($customer->otp));

        try {
            if (! $token = auth()->attempt($credentials['phone', 'password'])) {
            
                return response()->json([
                    'success' => false,
                    'errors' => ['Please check your credentials']
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to login, please try again.']
            ], 500);
        }

        return $this->respondWithToken($token, $customer);
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
        if(! $customer->active) {

            if($request->filled('jelion')) {
                $customer->jelion = $request->jelion;
                $customer->active = true;
                $customer->save();

                return response()->json($customer, 200);
            }

            return response()->json([
                'success' => false,
                'errors' => ['Please provide a jelion']
            ], 401);
        }

        $credentials = $request->all();

        //Validate update request
        $rules = [
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'phone' => 'bail|nullable|string|max:15|unique:customers',
            'email' => 'bail|nullable|string|email|max:50|unique:customers', //Email already exists
        ];

        $messages = [
            'phone.max' => 'Please provide a valid :attribute number.',
            'phone.unique' => ':attribute number has already been taken.',
            'email' => 'Please provide a valid email address.',
            'email.unique' => 'Email has already been taken'
        ];

        $validator = Validator::make($credentials, $rules, $messages);
        
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //Update Customer's details
        $customer->update($credentials);

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
    protected function respondWithToken($token, $details = "")
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'message' => 'Verification code has been sent.'
            //'expires_in' => auth()->factory()->getTTL(),
            'data' => $details
        ], 200);
    }
}
