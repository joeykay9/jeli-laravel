<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Validator;
use App\Otp;
use App\Customer;
use App\Mail\CustomerWelcome;
use App\Notifications\SendOTPNotification;
use App\Http\Controllers\API\ApiController;

class OtpController extends ApiController
{
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

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        if(! $customer->otp->verified) {
            $otp = $customer->otp->otp;

            if($request->otp != $otp) {
                return response()->json([
                    'success' => false, 
                    'errors' => ['Wrong pin entered']
                ], 401);
            }

            //Change status to verified:1
            $customer->otp->verified = true;
            $customer->otp->save();

            return response()->json([
                'success' => true,
                'message' => 'Customer has been verified',
                'verified' => $customer->otp->verified,
                'data' => $customer,
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
    public function requestOTP(Customer $customer) {

        $otp = new Otp;

        if($customer->otp) {
            $customer->otp()->update($otp->toArray());
            //Unverify customer
            $customer->otp->verified = false;
            $customer->otp->save();

            $customer->notify(new SendOTPNotification($otp));

            return response()->json([
                'success' => true,
                'message' => 'Verification code has been sent',
            ], 200);
        }

        dd($customer);
        //Create otp entry in database
        $customer->otp()->save($otp);

        //Send email with OTP to customer
        // if($customer->email){
        //     \Mail::to($customer)->send(new CustomerWelcome($otp));
        // }

        //Send new one time pin via SMS
        $customer->notify(new SendOTPNotification($otp));

        return response()->json([
            'success' => true,
            'message' => 'Verification code has been sent',
        ], 200);
    }
}
