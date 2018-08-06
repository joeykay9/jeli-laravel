<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Moment;
use App\ChatGroup;
use App\Customer;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class MomentController extends Controller
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
        //Return JSON array of JSON Moment objects
        return auth('api')->user()->moments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $credentials = $request->only([
            'category', 'title', 'date', 'time', 'location', 'budget'
        ]);

        $rules = [
            'category' => 'required|string',
            'title' => 'required|string|max:25',
            'date' => 'nullable|date', 
            'time' => 'nullble|date_format:H:i', 
            'location' => 'nullable|string',
            'budget' => 'nullable|decimal',
        ];

        $messages = [];

        $validator = Validator::make($credentials, $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all(),
            ], 422);
        }

        auth('api')->user()->createMoment($moment = 
            new Moment($credentials)
        );

        //Store in pivot table
        auth()->user()->moments()->attach($moment, ['is_organiser' => true, 'is_admin' => true]);

        if($request->filled('chat_group')) { //If chat group option specified
            if($request->chat_group) { // And it's true
                $moment->chatGroup()->save(new ChatGroup); //Create a chat group for the moment
            }
        }

    	return response()->json([
    		$moment], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function show(Moment $moment)
    {
        return $moment;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Moment $moment)
    {
        //Validate the request
        $input = $request->all();

        //Update the moment
        $moment->update($input);

        //Return the updated moment
        return $moment;
    }

    public function addOrgnisers(Request $request, Moment $moment)
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

        //Get Jeli Organisers
        $jeliOrganisers = Customer::whereIn('phone', $formattedPhoneNumbers)->get();

        $moment->members()->attach($jeliGuests, ['is_organiser' => true]);
    }

    public function addGuests(Request $request, Moment $moment)
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
        $jeliGuests = Customer::whereIn('phone', $formattedPhoneNumbers)->get();

        $moment->members()->attach($jeliGuests, ['is_guest' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Moment $moment)
    {
        $moment->forceDelete();

        return response()->json([
            "success" => true,
            "message" => "Moment has been successfully deleted"
        ]);
    }
}
