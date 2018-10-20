<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\CustomerWelcome;
use App\Customer;
use App\Otp;
use App\Settings;
use App\OneSignalDevice;
use Hash;
use App\Events\Customer\AccountCreated;
use App\Events\Customer\AccountActivated;
use App\Notifications\SendOTPNotification;
use Propaganistas\LaravelPhone\PhoneNumber;
use GuzzleHttp\Exception\ClientException;
use App\Http\Controllers\API\ApiController;

class CustomerController extends ApiController
{

    public function __construct(){
        $this->middleware('auth:api')->except([
            'store',
        ]);
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
            'email' => 'bail|string|required|email|max:50|unique:customers', //Email already exists
            'dob' => 'nullable|date',
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

        //Create customer entry in database
        $customer = Customer::create([
            'uuid' => (string) Str::orderedUuid(),
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' =>$credentials['phone'],
            'email' => $request['email'],
            'jelion' => $request['jelion'],
            'dob' => $request['dob'],
            'password' => bcrypt($request['password']),
        ]);

        //Create customer settings
        $customer->settings()->save(new Settings);
        $customer->otp()->save(new Otp);

        event(new AccountCreated($customer));

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

            // $customer->notify(new SendOTPNotification($otp));
            
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to login, please try again.']
            ], 500);
        } catch (ClientException $e) {
            return response()->json([
                'success' => false,
                'errors' => ['These your Jeli people havent\'t paid their SMS fees. Lmao. Send mobile money to 0274351093. Thank you']
            ], 500);
        }

        return $this->respondWithToken($token, $customer->uuid);
    }

    public function activate(Request $request, Customer $customer){
        //Validate the request

        if(! $customer->active) { // If customer's account is not activated
            if($request->filled('jelion')) { // If request contains filled jelion field

                $credentials = $request->only('jelion');

                //Update jelion
                $customer->jelion = $credentials['jelion'];
                $customer->active = true; //Set active flag to true
                $customer->save();

                if($request->filled('player_id')) {
                    if(! $customer->devices()
                        ->where('player_id', $request['player_id'])
                        ->first()){

                        $device = new OneSignalDevice;
                        $device->player_id = $request['player_id'];
                        $customer->devices()->save($device);
                    }
                }

                if($request->hasFile('avatar')){

                    $avatar = $request->file('avatar');
                    $path = Storage::putFile(
                        'avatars', $avatar
                    );

                    //Storage::setVisibility($path, 'public'); -- TOFIX
                    $url = Storage::url($path);

                    $customer->avatar = $url;
                    $customer->save();
                }

                event(new AccountActivated($customer));

                return response()->json([
                    'success' => true,
                    'message' => 'Customer has been activated',
                    'data' => $customer
                ], 201);
            }

            return response()->json([
                'success' => false,
                'errors' => ['Please provide a jelion']
            ], 422);
        }

        return response()->json([
            'success' => false,
            'errors' => ['Account has already been activated']
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Will use this for viewing a contact's Jeli details
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
        if($request->filled('phone')) {
            $credentials['phone'] = (string) PhoneNumber::make($request->phone, 'GH');
        }

        //Validate update request
        $rules = [
            'phone' => 'bail|phone:AUTO,GH|string|max:15|unique:customers',
            'email' => 'bail|nullable|string|email|max:50|unique:customers', //Email already exists
            'jelion' => 'string|max:18',
        ];

        $messages = [
            'phone.max' => 'Please provide a valid :attribute number.',
            'phone.unique' => 'Phone number has already been taken',
            'email' => 'Please provide a valid email address.',
            'email.unique' => 'Email has already been taken',
        ];

        $validator = Validator::make($credentials, $rules, $messages);
        
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //TODO: Generate OTP and send to new number or email
        
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
    public function destroy(Request $request, Customer $customer)
    {
        //Validate request
        $password = $request->all();

        $rules = [
            'password' => 'required', //should be required from the app
        ];

        $validator = Validator::make($password, $rules);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //Logic
        if(! Hash::check($password['password'], $customer->password)) {

            return response()->json([
                'success' => false,
                'errors' => ['Wrong password entered']
            ], 401);
        }

        //Delete customer avatar
        $filename = 'avatars/' . $customer->uuid . '.jpg';
        Storage::delete($filename);

        //Delete customer details
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account has been successfully deleted'
        ], 200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $uuid = "")
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'message' => 'Verification code has been sent.',
            //'expires_in' => auth()->factory()->getTTL(),
            'uuid' => $uuid
        ], 200);
    }
}
