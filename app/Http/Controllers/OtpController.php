<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Otp;
use App\Customer;
use App\Mail\CustomerWelcome;
use App\Notifications\SendOTPNotification;

class OtpController extends Controller
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
    public function requestNewOTP(Customer $customer) {

    	$newOtp = new Otp;
        $customer->otp()->update($newOtp->toArray());

        //Send email with OTP to customer
        if($customer->email){
            \Mail::to($customer)->send(new CustomerWelcome($newOtp));
        }

        //Send new one time pin via SMS
        $customer->notify(new SendOTPNotification($newOtp));

        return response()->json([
            'success' => true,
            'message' => 'New verification code has been sent',
        ], 200);
    }
}
