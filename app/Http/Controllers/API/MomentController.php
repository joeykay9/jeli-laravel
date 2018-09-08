<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Moment;
use App\ChatGroup;
use App\Customer;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Facades\Storage;

class MomentController extends Controller
{
    public function __construct(){
    	$this->middleware('auth:api');
        $this->middleware('moment.creator')->only([
            'update', 'destroy'
        ]);
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
            'category', 'title', 'date', 'time', 'location', 'budget', 'chat_group'
        ]);

        $rules = [
            'category' => 'required|string',
            'title' => 'required|string|max:25',
            'date' => 'nullable|date', 
            'time' => 'nullable|date_format:H:i', 
            'location' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'chat_group' => 'boolean',
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

        if($request->hasFile('icon')){

            $icon = $request->file('icon');
            $path = Storage::putFile(
                'icons', $icon
            );

            //Storage::setVisibility($path, 'public'); -- TOFIX
            $url = Storage::url($path);

            $moment->icon = $url;
            $moment->save();
        }

        //Store in pivot table
        auth()->user()->moments()->attach($moment, ['is_organiser' => true, 'is_grp_admin' => true]);

        if($request->filled('chat_group')) { //If chat group option specified
            if($request->chat_group) { // And it's true
                $moment->chatGroup()->save(new ChatGroup); //Create a chat group for the moment
            }
        }

    	return response()->json(
    		$moment, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Moment $moment)
    {
        return $auth('api')->user()->moments()->where('id', $moment->id)->first();
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
