<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Moment;
use App\Customer;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class MomentGuestController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json($moment->members()->wherePivot('is_guest', true)->get()); //List all guests of a moment
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Moment $moment)
    {
        $credentials = $request->all();
        $phoneNumbers = array();

        //Extract phone numbers from request
        foreach ($credentials as $data => $array) {
            foreach ($array as $index => $contacts){
                foreach ($contacts as $key => $value) {
                    if($key == "contactNumber")
                        $phoneNumbers[] = $value;
                }
            }
        }

        $formattedPhoneNumbers = array();

        //Format phone numbers
        foreach ($phoneNumbers as $key => $value) {
            $formattedPhoneNumbers[] = (string) PhoneNumber::make($value, 'GH');
        }

        //Get Jeli Guests
        $jeliGuestsOnJeli = Customer::whereIn('phone', $formattedPhoneNumbers)->get();

        $moment->members()->attach($jeliGuestsOnJeli, ['is_guest' => true]);

        //Extract numbers of those not on jeli from formatedPhoneNumbers array
        $jeliGuestsNotOnJeli = Customer::whereNotIn('phone', $formattedPhoneNumbers)->get();

        dd($jeliGuestsNotOnJeli->toArray());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Moment  $moment
     * @param  App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function removeGuest(Moment $moment, Customer $customer)
    {

        if($moment->members()
            ->wherePivot('is_guest', true)->get()
            ->where('uuid', $customer->uuid)){

            $result = $moment->members()->detach($customer);

            if($result) { //If record was deleted
                return response()->json([
                    'success' => true,
                    'message' => 'Guest successfully removed'
                ], 200);
            }
        }
        
        return response()->json([
            'success' => false,
            'errors' => ['Guest not found']
        ], 404);
    }
}
