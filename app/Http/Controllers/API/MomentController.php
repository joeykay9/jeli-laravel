<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Moment;
use App\ChatGroup;
use App\Customer;
use App\Place;
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
        $moments = [];

        foreach (auth('api')->user()->moments as $moment) {
            $moments[] = [
                "id" => $moment->id,
                "category" => $moment->category,
                "title" => $moment->title,
                "date" => $moment->date,
                "time" => $moment->time,
                "place_id" => $moment->place->place_id,
                "place_name" => $moment->place->place_name,
                "budget" => $moment->budget,
                "icon" => $moment->icon,
                "is_memory" => $moment->is_memory,
            ];
        }

        //Return JSON array of JSON Moment objects
        return response()->json($moments);
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
            'category', 'title', 'date', 'time', 'budget',
        ]);

        $rules = [
            'category' => 'required|string',
            'title' => 'required|string|max:25',
            'date' => 'nullable|date', 
            'time' => 'nullable|date_format:H:i', 
            'place_id' => 'nullable|string',
            'place_name' => 'nullable|string',
            'budget' => 'nullable|numeric',
        ];

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all(),
            ], 422);
        }

        auth('api')->user()->createMoment($moment = 
            new Moment($credentials)
        );

        //Create Place Object
        $place = new Place([
            'place_id' => $request->place_id,
            'place_name' => $request->place_name,
        ]);

        //Save Place Record
        $moment->place()->save($place);

        dd($place);

        //Store in pivot table
        auth()->user()->moments()->attach($moment, ['is_organiser' => true, 'is_grp_admin' => true]);

        event(new MomentCreated($moment)); //Fire Moment Created event

        // if($request->hasFile('icon')){
 
        //      $icon = $request->file('icon');
        //      $path = Storage::putFile(
        //          'icons', $icon
        //      );
 
        //      //Storage::setVisibility($path, 'public'); -- TOFIX
        //      $url = Storage::url($path);
 
        //      $moment->icon = $url;
        //      $moment->save();
        //  }

        $moment->chatGroup()->save(new ChatGroup); //Create a chat group for the moment

    	return response()->json([
            "id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            "date" => $moment->date,
            "time" => $moment->time,
            "place_id" => $moment->place->place_id,
            "place_name" => $moment->place->place_name,
            "budget" => $moment->budget,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Moment $moment)
    {
        //return auth('api')->user()->moments()->where('id', $moment->id)->first();

        return response()->json([
            "id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            "date" => $moment->date,
            "time" => $moment->time,
            "place_id" => $moment->place->place_id,
            "place_name" => $moment->place->place_name,
            "budget" => $moment->budget,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
        ], 201);
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
        $input = $request->except(['place_id', 'place_name']);
        $place = $request->only(['place_id', 'place_name']);

        //Update the moment
        $moment->update($input);

        if($place) {
            $moment->place->place_id = $place->place_id;
            $moment->place->place_name = $place->place_name;
            $moment->place->save();
        }

        //Return the updated moment
        return response()->json([
            "id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            "date" => $moment->date,
            "time" => $moment->time,
            "place_id" => $moment->place->place_id,
            "place_name" => $moment->place->place_name,
            "budget" => $moment->budget,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
        ], 201);
    }

    public function end(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if(! $moment->is_memory) {
            $moment->is_memory = true;
            $moment->save();
        }

        return response()->json([
            "id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            "date" => $moment->date,
            "time" => $moment->time,
            "place_id" => $moment->place->place_id,
            "place_name" => $moment->place->place_name,
            "budget" => $moment->budget,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
        ], 201);
    }

    public function restore(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if($moment->is_memory) {
            $moment->is_memory = false;
            $moment->save();
        }

        return response()->json([
            "id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            "date" => $moment->date,
            "time" => $moment->time,
            "place_id" => $moment->place->place_id,
            "place_name" => $moment->place->place_name,
            "budget" => $moment->budget,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
        ], 201);
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
