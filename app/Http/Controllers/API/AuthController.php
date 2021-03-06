<?php

namespace App\Http\Controllers\API;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Validator, Hash;
use App\Customer;
use App\OneSignalDevice;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Notifications\SendOTPNotification;
use App\Mail\CustomerWelcome;
use GuzzleHttp\Exception\ClientException;
use App\Http\Controllers\API\ApiController;

class AuthController extends ApiController
{

    /**
     * Create a new AuthController instance.
     *
     * @return vpopmail_del_domain(domain)
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'forgotPassword', 'resetPassword']]);
    }

    protected function attemptLoginByCredentials($username, $password) {

        if(filter_var($username, FILTER_VALIDATE_EMAIL)){
                    if (! $token = auth()->attempt([
                        'email' => $username,
                        'password' => $password,
                    ])) {
                
                        return $token;
                    }
                } else {
                    if (! $token = auth()->attempt([
                        'phone' => $username, 
                        'password' => $password,
                    ])) {
                
                        return $token;
                    }
                }

        return $token;
    }

    public function verify(Request $request)
    {
        auth('api')->user()->verified = 1;
        auth('api')->user()->save();

        return response()->json([
            'success' => true,
            'message' => 'Phone number successfully verified'
        ]);
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

        //Check if Customer exists
        if(filter_var($request->username, FILTER_VALIDATE_EMAIL)){
            $customer = Customer::where('email', $request->username)->first();
        } else {
            $request->username = (string) PhoneNumber::make($request->username, 'GH');
            $customer = Customer::where('phone', $request->username)->first();
        }

        //Return failed response if customer does not exist
        if (! $customer) {
            return response()->json([
                    'success' => false,
                    'errors' => ['Incorrect username or password. Please check your credentials.']
                ], 401); //401: Unauthorized
        }

        //Try loggin in customer
        try {
            
            $token = $this->attemptLoginByCredentials($credentials['username'], $credentials['password']);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to login, please try again.']
            ], 500);
        }

        //If token is false, then wrong login credentials namely password entered
        if(! $token){
            return response()->json([
                    'success' => false,
                    'errors' => ['Incorrect username or password. Please check your credentials.']
                ], 401); //401: Unauthorized
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
    public function logout(Request $request)
    {
        $player_id = $request->only('player_id');

        $rules = [
            'player_id' => 'required|string',
        ];

        $validator = Validator::make($player_id, $rules);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        $customerDevice = auth()->user()->devices()
                            ->where('player_id', $player_id)
                            ->first();
        // dd($customerDevice);
        if($customerDevice) {
            $customerDevice->logged_in = false;
            $customerDevice->save();
        }
        
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
            //'expires_in' => auth()->factory()->getTTL(),
            'data' => $details
        ], $code);
    }
}
