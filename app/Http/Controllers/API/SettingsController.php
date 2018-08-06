<?php

namespace App\Http\Controllers\API;

use App\Settings;
use App\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{

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
