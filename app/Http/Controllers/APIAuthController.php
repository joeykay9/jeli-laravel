<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Validator, Hash;
use App\Customer;
use App\Otp;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Notifications\SendOTPNotification;
use App\Mail\CustomerWelcome;
use GuzzleHttp\Exception\ClientException;

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
     * @return vpopmail_del_domain(domain)
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'forgotPassword', 'resetPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if($request->filled('username')) {
            if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){

                $rules = [
                    'username' => 'required|email',
                    'password' => 'required',
                ];
            } else {
                $credentials['username'] = (string) PhoneNumber::make($request->username, 'GH');

                $rules = [
                    'username' => 'required|phone:AUTO,GH',
                    'password' => 'required',
                ];
            }
        }

        $messages = [
            'password' => 'The :attribute field is required.',
        ];

        $validator = Validator::make($credentials, $rules, $messages);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
            $customer = Customer::where('email', $credentials['username'])->first();
        } else {
            $customer = Customer::where('phone', $credentials['username'])->first();
        }

        if(! $customer) {
            return response()->json([
                    'success' => false,
                    'errors' => ['Incorrect username or password. Please check your credentials.']
                ], 401); //401: Unauthorized
        }

        if(! $customer->otp->verified) {

            //Send OTP to Customers phone via SMS
            try {
                $otp = new Otp;
                $customer->otp()->update($otp->toArray());

                if($customer->email) {
                    \Mail::to($customer)->send(new CustomerWelcome($otp));
                }

                //$customer->notify(new SendOTPNotification($otp));
                if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
                    if (! $token = auth()->attempt([
                        'email' => $credentials['username'], 
                        'password' => $credentials['password'],
                    ])) {
                
                        return response()->json([
                            'success' => false,
                            'errors' => ['Please check your credentials']
                        ], 401);
                    }
                } else {
                    if (! $token = auth()->attempt([
                        'phone' => $credentials['username'], 
                        'password' => $credentials['password'],
                    ])) {
                
                        return response()->json([
                            'success' => false,
                            'errors' => ['Please check your credentials']
                        ], 401);
                    }
                }

            } catch (ClientException $e) {
                return response()->json([
                    'success' => false,
                    'errors' => ['These your Jeli people havent\'t paid their SMS fees. Lmao. Send mobile money to 0274351093. Thank you']
                ], 500);
            } catch (JWTException $e) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Failed to login, please try again.']
                ], 500);
            }

            //Customer has not been verified
            return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'message' => 'Verification code has been sent, please verify your account.',
                    'verified' => $customer->otp->verified,
                    //'expires_in' => auth()->factory()->getTTL(),
                    'data' => $customer
                ], 200);
        }

        try {
            if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
                    if (! $token = auth()->attempt([
                        'email' => $credentials['username'], 
                        'password' => $credentials['password'],
                    ])) {
                        
                        return response()->json([
                            'success' => false,
                            'errors' => ['Please check your credentials']
                        ], 401);
                    }
                } else {
                    if (! $token = auth()->attempt([
                        'phone' => $credentials['username'], 
                        'password' => $credentials['password'],
                    ])) {
                
                    return response()->json([
                        'success' => false,
                        'errors' => ['Please check your credentials']
                    ], 401);
                }
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

        //Get Customer
        $customer = Customer::where('phone', $credentials['phone'])->first();

        if(! $customer) {
            return response()->json([
                'success' => false,
                'errors' => ['Account does not exist.'],
            ], 404);
        }

        

        //Send OTP via E-mail and or SMS to verify phone number
        try {

            //Generate OTP
            $otp = new Otp;

            $customer->otp()->update($otp->toArray());
            $customer->otp->verified = false;
            $customer->otp->save();

            //Send email with OTP to customer
            if($customer->email){
                \Mail::to($customer)->send(new CustomerWelcome($otp));
            }

            //$customer->notify(new SendOTPNotification($otp));

            return response()->json([
                'success' => true,
                'message' => 'Verification code has been sent.'
            ], 200,
            [ 
                'Location' => $customer->id,
            ]);

        } catch (ClientException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['These your Jeli people havent\'t paid their SMS fees. Lmao. Send mobile money to 0274351093. Thank you']
            ], 500);
        }
    }

    public function resetPassword(Request $request) {
        //Validate the request
        $credentials = $request->all();

        if($request->filled('phone')) {
            $credentials['phone'] = (string) PhoneNumber::make($request->phone, 'GH');
        }

        $rules = [
            'phone' => 'required|phone:AUTO,GH',
            'password' => 'required|confirmed'
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

        if(! $customer) { //I don't expect this condition to ever be true
            return response()->json([
                'success' => false,
                'errors' => ['Account does not exist.'],
            ], 404);
        }

        //update password
        $customer->password = Hash::make($request->password);
        $customer->save();

        //Log customer in
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

        return $this->respondWithToken($token, $customer, 201);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $details = "", $code = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'verified' => $details->otp->verified,
            //'expires_in' => auth()->factory()->getTTL(),
            'data' => $details
        ], $code);
    }
}
