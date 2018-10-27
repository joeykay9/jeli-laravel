<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Contact;
use App\Transformers\ContactTransformer;
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
        $phoneNumbers = array();

        //Extract phone numbers from request
        foreach ($credentials as $data => $array) {
            foreach ($array as $index => $contacts){
               	foreach ($contacts as $key => $value) {
                    if($key == "contactName")
                        $name = $value;
                    if($key == "contactNumber") {
                    	$phone = (string) PhoneNumber::make($value, 'GH');
                        $jeliCustomer = Customer::where('phone', $phone);
                    }

                    if($jeliCustomer) {
                    	$contact = new Contact([
	                    	'uuid' => $jeliCustomer->uuid,
	                        'name' => $name,
	                        'phone' => $jeliCustomer->phone,
	                        'avatar' => $jeliCustomer->avatar,
	                    ]);

	                    $customer->contacts()->save($contact);
                    }
                }
        	}
    	}
    }
}
