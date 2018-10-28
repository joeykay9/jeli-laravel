<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Transformers\ContactTransformer;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Customer;

class ContactController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Customer $customer) {
	    $contacts = $customer->contacts()->take(10)->get();

        return $this->respondWithCollection($contacts, new ContactTransformer);
    }

    public function sync(Request $request, Customer $customer) {
    	$credentials = $request->all();
        $contactList = array();

        //Extract phone numbers from request
        foreach ($credentials as $data => $array) {
            foreach ($array as $index => $contacts){
            	$name = $contacts['contactName'];
            	$phone = (string) PhoneNumber::make($contacts['contactNumber'], 'GH');
            	$jeliCustomer = Customer::where('phone', $phone)->first();

            	if($jeliCustomer) {
                    $contactList[$jeliCustomer->id] = ['contact_name' => $name];
                }
        	}
    	}

        $customer->contacts()->sync($contactList);

    	return response()->json([
    		'success' => true,
    		'message' => 'Your contacts have been successfully synced'
    	], 201);
    }
}
