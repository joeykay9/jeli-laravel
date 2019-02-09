<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Moment;
use App\ChatGroup;
use App\Customer;
use App\Place;
use App\Schedule;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Events\Customer\MomentCreated;
use App\Http\Controllers\API\ApiController;
use App\Transformers\MomentTransformer;
use App\Transformers\ScheduleTransformer;
use App\Serializers\JeliSerializer;
use League\Fractal\Manager;

class MomentController extends ApiController
{
    public function __construct(Manager $fractal){
        parent::__construct($fractal);

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
        $moments = auth('api')->user()->moments()->get(); //will add take(10) later

        return $this->respondWithCollection($moments, new MomentTransformer);
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
            'category', 'title', 'budget',
        ]);

        $rules = [
            'category' => 'required|string',
            'title' => 'required|string|max:25', 
            'place_id' => 'nullable|string',
            'place_name' => 'nullable|string',
            'schedule' => 'required|array|min:1',
            '*.*.start_date' => 'required|date_format:"d-M-Y"',
            '*.*.end_date' => 'nullable|date_format:"d-M-Y"',
            '*.*.start_time' => 'nullable|date_format:"H:i"',
            '*.*.end_time' => 'nullable|date_format:"H:i"|after:*.*.start_time',
            'budget' => 'nullable|string',
        ];

        $messages = [
            '*.*.start_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.*.end_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.*.end_time.after' => 'The end time must be a time after the start time',
            '*.*.start_time.date_format' => 'Please enter a valid time in the format H:i (e.g. 16:43)',
            '*.*.end_time.date_format' => 'Please enter a valid time in the format H:i (e.g. 16:43)',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all(),
            ], 422);
        }

        auth('api')->user()->createMoment($moment = 
            new Moment($credentials)
        ); //User creates a moment

        //Storing place details
        if($request->filled('place_id') && 
            $request->filled('place_name')){
            //Create Place Object
            $place = Place::where('place_id', $request->place_id)                ->first();

            if(!$place) {
                $place = Place::create([
                    'place_id' => $request->place_id,
                    'place_name' => $request->place_name,
                    'place_image' => $request->place_image,
                ]);
            }

            //Save Place Record
            $place->moments()->save($moment);
        }

        //Storing schedule details
        if ($request->filled('schedule')) {
            foreach ($request['schedule'] as $schedule) {

                $moment->schedules()->save(new Schedule($schedule));
            }
        }

        //Store in pivot table
        auth()->user()->moments()->attach($moment, ['is_organiser' => true, 'is_grp_admin' => true]);

        event(new MomentCreated($moment)); //Fire Moment Created event
        
        $moment->chatGroup()->save(new ChatGroup); //Create a chat group for the moment

        return $this->respondWithItem($moment, new MomentTransformer)->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function show(Moment $moment)
    {
        return $this->respondWithItem($moment, new MomentTransformer);
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
        $input = $request->except(['place_id', 'place_name', 'place_image']);
        $placeExists = $request->filled([
            'place_id', 'place_name'
        ]);
        $placeData = $request->only([
            'place_id', 'place_name', 'place_image' 
        ]);

        //Update the moment
        if($input) {
            $moment->update($input);
        }

        if($placeExists) {
            if(! $moment->place()->first()){ //If moment does not have place
                //Check if place data already exists in db
                $place = Place::where('place_id', $placeData['place_id'])->first();

                if($place) { //If place data already exists in db
                    // Assign place to moment
                    $place->moments()->save($moment);
                } else { //create new place from place data and assign to moment
                    $newPlace = Place::create($placeData);
                    $moment->update([
                        'place_id' => $newPlace->id,
                    ]);
                }

            } else { //If moment already has place
                $moment->place->place_id = $request->input('place_id');
                $moment->place->place_name = $request->input('place_name');
                $moment->place->place_image = $request->input('place_image');
                $moment->place->save();
            }
        }

        //Return the updated moment
        return $this->respondWithItem($moment, new MomentTransformer);
    }

    public function updateSchedules(Request $request, Moment $moment)
    {
        $input = $request->all();

        $rules = [
            '*.start_date' => 'required|date_format:"d-M-Y"',
            '*.end_date' => 'nullable|date_format:"d-M-Y"|after:*.start_date',
            '*.start_time' => 'nullable|date_format:"H:i"',
            '*.end_time' => 'nullable|date_format:"H:i"',
        ];

        $messages = [
            '*.start_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.end_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.end_date.after' => 'The end date must be a date after the start date',
            '*.start_time.date_format' => 'Please enter a valid time in the format H:i (e.g. 16:43)',
            '*.end_time.date_format' => 'Please enter a valid time in the format H:i (e.g. 16:43)',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()->all(),
            ], 422);
        }

        //Delete schedule records whose ids are not in request body
        $ids = array_column($input, 'id'); //ids in request body
        Schedule::where('moment_id', $moment->id)
                ->whereNotIn('id', $ids)
                ->delete();

        foreach($input as $scheduleUpdate) { //for each schedule
            if(! array_key_exists('id', $scheduleUpdate)) { //if id doesn't exist, it means a new schedule is being added

                $newSchedule = Schedule::make($scheduleUpdate);
                $moment->schedules()->save($newSchedule);
            } else { //it's an existing schedule
                
                $schedule = Schedule::where('moment_id', $moment->id)
                                ->where('id', $scheduleUpdate['id'])->first();
            
                $schedule->update($scheduleUpdate);
            }
        }

        return $this->respondWithItem($moment, new MomentTransformer);
    }

    public function end(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if(! $moment->is_memory) {
            $moment->is_memory = true;
            $moment->save();
        }

        return $this->respondWithItem($moment, new MomentTransformer);
    }

    public function restore(Request $request, Moment $moment)
    {
        $input = $request->only('is_memory');

        if($moment->is_memory) {
            $moment->is_memory = false;
            $moment->save();
        }

        return $this->respondWithItem($moment, new MomentTransformer);
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
