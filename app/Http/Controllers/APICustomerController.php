<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Mail\CustomerWelcome;
use App\Customer;
use App\Otp;
use App\Notifications\SendOTPNotification;
use Propaganistas\LaravelPhone\PhoneNumber;
use GuzzleHttp\Exception\ClientException;

class APICustomerController extends Controller
{
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
        $otp = new Otp;

        //Create customer entry in database
        $customer = Customer::create([
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' =>$credentials['phone'],
            'email' => $request['email'],
            'jelion' => $request['jelion'],
            'password' => bcrypt($request['password']),
        ]);

        //Create otp entry in database
        $customer->otp()->save($otp);

        //Send email with OTP to customer
        if($request->filled('email')) {
            \Mail::to($customer)->send(new CustomerWelcome($otp));
        }

        //Send OTP to Customers phone via SMS
        try {
            $customer->notify(new SendOTPNotification($otp));
        } catch (ClientException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['These your Jeli people havent\'t paid their SMS fees. Lmao. Send mobile money to 0274351093. Thank you']
            ], 500);
        }

        try {
            if (! $token = auth()->attempt([
                'phone' => $credentials['phone'],
                'password' => $credentials['password'],
            ])) {
            
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

        return $this->respondWithToken($token, $customer->id);
    }

    public function activate(Request $request, Customer $customer){

        if(! $customer->active) { // If customer's account is not activated
            if($request->filled('jelion')) { // If request contains filled jelion field

                $credentials = $request->only('jelion');

                //Update jelion
                $customer->jelion = $credentials;
                $customer->active = true; //Set active flag to true
                $customer->save();

                return response()->json($customer, 201);
            }

            return response()->json([
                'success' => false,
                'errors' => ['Please provide a jelion']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account has already been activated'
        ], 401);
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
    protected function respondWithToken($token, $id = "")
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'message' => 'Verification code has been sent.',
            //'expires_in' => auth()->factory()->getTTL(),
            'id' => $id
        ], 200);
    }
}
