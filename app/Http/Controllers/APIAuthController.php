<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Validator;
use App\Customer;
use Propaganistas\LaravelPhone\PhoneNumber;

class APIAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
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
                    'errors' => ['Please check your credentials']
                ], 401); //401: Unauthorized
        }

        if(! $customer->verified) {
            //Customer has not been verified
            return response()->json([
                    'success' => false,
                    'errors' => ['Customer has not been verified']
                ], 401, //401: Unauthorized
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
