<?php

namespace App\Http\Controllers\API;

use App\Settings;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\ApiController;

class SettingsController extends ApiController
{

    public function __construct(){
        $this->middleware('auth:api');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function show(Settings $settings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $credentials = $request->all();

        $rules = [
            'read_receipts' => 'boolean',
            'live_location' => 'boolean',
        ];

        $validator = Validator::make($credentials, $rules);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        $customer->settings()->update($credentials);

        return response()->json([
            'success' => true,
            'message' => 'Settings have been updated.',
        ], 200);
    }
}
