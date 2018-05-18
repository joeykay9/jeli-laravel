<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Moment;

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
        $this->validate(request(), [
    		'category' => 'required',
    		'title' => 'required'
    	]);

        auth('api')->user()->createMoment($moment = 
            new Moment(request(['category', 'title']))
        );

        //Store in pivot table
        auth()->user()->moments()->attach($moment);

    	return response()->json([
    		'data' => $moment,
    	], 201);
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
