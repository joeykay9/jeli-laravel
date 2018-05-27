<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Validator;
use App\Customer;
use App\Otp;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Notifications\SendOTPNotification;
use App\Mail\CustomerWelcome;

class APIAuthController extends Controller
{

    protected $OTP;

    protected function generateOTP() {
        $this->OTP = mt_rand(100000, 999999);

        return $this->OTP;
    }

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'forgotPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if($request->filled('phone')) {
            $credentials['phone'] = (string) PhoneNumber::make($request->phone, 'GH');
        }

        $rules = [
            'phone' => 'required|phone:AUTO,GH',
            'password' => 'required',
        ];

        $messages = [
            'phone.required' => 'The :attribute number field is required.',
            'password' => 'The :attribute field is required.',
        ];

        $validator = Validator::make($credentials, $rules, $messages);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        $customer = Customer::where('phone', $credentials['phone'])->first();

        if(! $customer) {
            return response()->json([
                    'success' => false,
                    'errors' => ['Incorrect email or password. Please check your credentials.']
                ], 401); //401: Unauthorized
        }

        if(! $customer->otp->verified) {
            //Customer has not been verified
            return response()->json([
                    'success' => false,
                    'errors' => ['Customer has not been verified']
                ], 204,
                [ 
                    'Location' => '/customers/'. $customer->id,
                ]);
        }

        try {
            if (! $token = auth()->attempt($credentials)) {
            
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
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function forgotPassword(Request $request) {
        //Validate Request
        $credentials = $request->only('phone');

        if($request->filled('phone')) {
            $credentials['phone'] = (string) PhoneNumber::make($request->phone, 'GH');
        }

        $rules = [
            'phone' => 'bail|required|phone:AUTO,GH'
        ];

         $messages = [
            'phone.required' => 'The :attribute number field is required.'
        ];

        $validator = Validator::make($credentials, $rules, $messages);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //Generate OTP
        $otp = new Otp;

        //Get Customer
        $customer = Customer::where('phone', $credentials['phone'])->first();

        if(! $customer) {
            return response()->json([
                'success' => false,
                'errors' => ['Account does not exist.'],
            ], 404);
        }

        $customer->otp()->update($otp->toArray());

        //Send email with OTP to customer
        if($customer->email){
            \Mail::to($customer)->send(new CustomerWelcome($otp));
        }

        //Send OTP via E-mail and or SMS to verify phone number
        $customer->notify(new SendOTPNotification($otp));

        //Return response
        return response()->json([
            'success' => true,
            'message' => 'Verification code has been sent.'
        ], 200,
        [ 
            'Location' => '/customers/'. $customer->id,
        ]);
    }

    public function resetPassword(Request $request, Customer $customer) {
        //Validate the request
        $password = $request->all();

        $rules = [
            'password' => 'required|confirmed'
        ];

        $validator = Validator::make($password, $rules);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        $customer->fill([
            'password' => Hash::make($request->password)
        ])->save();

        return response()->json([
                'success' => true,
                'message' => 'Your password has been reset.'
            ], 200);
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
            //'expires_in' => auth()->factory()->getTTL(),
            'data' => $details
        ], 200);
    }
}
