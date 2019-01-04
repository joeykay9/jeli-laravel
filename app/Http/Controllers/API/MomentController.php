<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Moment;
use App\ChatGroup;
use App\Customer;
use App\Place;
use App\Schedule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Events\Customer\MomentCreated;
use App\Http\Controllers\API\ApiController;
use App\Transformers\MomentTransformer;
use App\Serializers\JeliSerializer;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;

class MomentController extends ApiController
{
    protected $fractal;

    public function __construct(Manager $fractal){
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new JeliSerializer());

        if(Route::current()->getName() == 'moments.index'){
            $this->fractal->parseExcludes(['schedules', 'members']); //exclude shedules and members data from moment index response
        }

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
            '*.*.start_date' => 'required|date_format:"d-M-Y"|before:*.*.end_date',
            '*.*.end_date' => 'nullable|date_format:"d-M-Y"|after:*.*.start_date',
            '*.*.start_time' => 'nullable|date_format:"H:i"',
            '*.*.end_time' => 'nullable|date_format:"H:i"',
            'budget' => 'nullable|string',
        ];

        $messages = [
            '*.*.start_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.*.start_date.before' => 'The start date must be a date before the end date',
            '*.*.end_date.date_format' => 'Please enter a valid date in the format dd-mmm-yyyy (e.g. 02-Feb-2032)',
            '*.*.end_date.after' => 'The end date must be a date after the start date',
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
        );

        //Storing place details
        if($request->filled('place_id') && $request->filled('place_name')){
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
        $place = $request->filled(['place_id', 'place_name']);

        //Update the moment
        if($input) {
            $moment->update($input);
        }

        if($place) {
            $moment->place->place_id = $request->input('place_id');
            $moment->place->place_name = $request->input('place_name');
            $moment->place->place_image = $request->input('place_image');
            $moment->place->save();
        }

        //Return the updated moment
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
