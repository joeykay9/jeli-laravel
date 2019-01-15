<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Transformers\ContactTransformer;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Facades\Validator;
use App\Customer;

class ContactController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Customer $customer) {
	    $contacts = $customer->contacts()->take(20)->get();

        return $this->respondWithCollection($contacts, new ContactTransformer);
    }

    public function sync(Request $request, Customer $customer) {
    	$credentials = $request->all();
        $contactList = array();

        $rules = [
            '*.contact_name' => 'required|string',
            '*.contact_phone' => 'required|string',
        ];

        $validator = Validator::make($credentials, $rules);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all()
            ], 422);
        }

        //Extract phone numbers from request
        foreach ($credentials as $index => $contacts){

            if(! (array_key_exists('contact_name', $contacts) && array_key_exists('contact_phone', $contacts))) {
                return response()->json([
                    'success' => false,
                    'errors' => ['contact_name & contact_phone fields are both required']
                ], 422);
            }

        	$name = $contacts['contact_name'];
        	$phone = (string) PhoneNumber::make($contacts['contact_phone'], 'GH');
        	$jeliCustomer = Customer::where('phone', $phone)
                                    ->where('phone', '<>', $customer->phone)
                                    ->first();

        	if($jeliCustomer) {
                $contactList[$jeliCustomer->id] = ['contact_name' => $name];
            }
    	}

        $customer->contacts()->syncWithoutDetaching($contactList);

        $jeliContacts = $customer->contacts()->take(20)->get();

        return $this->respondWithCollection($jeliContacts, new ContactTransformer);
    }
}
