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
use App\Events\Customer\MomentCreated;

class MomentController extends Controller
{
    public function __construct(){
    	$this->middleware('auth:api');
        $this->middleware('moment.creator')->only([
            'update', 'destroy', 'end'
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
            'category', 'title', 'date', 'time', 'location', 'budget',
        ]);

        $rules = [
            'category' => 'required|string',
            'title' => 'required|string|max:25',
            'date' => 'nullable|date', 
            'time' => 'nullable|date_format:H:i', 
            'location' => 'nullable|string',
            'budget' => 'nullable|numeric',
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
        auth()->user()->moments()->attach($moment, ['is_organiser' => true, 'is_grp_admin' => true]);

        event(new MomentCreated($moment)); //Fire Moment Created event

        $moment->chatGroup()->save(new ChatGroup); //Create a chat group for the moment

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

    public function end(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if(! $moment->is_memory) {
            $moment->is_memory = true;
            $moment->save();
        }

        return $moment;
    }

    public function restore(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if($moment->is_memory) {
            $moment->is_memory = false;
            $moment->save();
        }

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
