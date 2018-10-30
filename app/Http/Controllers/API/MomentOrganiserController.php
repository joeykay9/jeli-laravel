<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Moment;
use App\Customer;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Http\Controllers\API\ApiController;
use App\Events\Customer\MomentOrganisersAdded;

class MomentOrganiserController extends ApiController
{

    public function __construct(){
        $this->middleware('auth:api');
        $this->middleware('moment.organiser')->only([
            'index', 'destroy'
        ]);
        $this->middleware('moment.admin')->only([
            'store', 'updateAdminStatus', 'removeOrganiser'
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Moment $moment)
    {
        return response()->json([
            $moment->members()->wherePivot('is_organiser', true)->get()
        ], 200); //List all organisers of a moment's Jelispace
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

        $array = $credentials['data'];
        $uuids = array_values(array_dot($array));

        $organisers = Customer::whereIn('uuid', $uuids)->get(); //Get Customers from submitted uuids

        $moment->members()->attach($organisers, ['is_organiser' => true]);

        //Code to handle if customer is already an organiser on this moment or have Nathany do it at his end...On add particiapnts to a group Whatsapp replaces their status with 'Already added to the group' when the contact list loads

        event(new MomentOrganisersAdded(auth('api')->user(), $moment, $organisers));

        return response()->json([
            'success' => true,
            'message' => "Organisers have been added",
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Moment  $moment
     * @return \Illuminate\Http\Response
     */
    public function updateAdminStatus(Request $request, Moment $moment, Customer $customer)
    {
        //Validate the request
        $admin = $request->only('admin');

        if(! $admin) { //If remove as admin
            $result = $moment->members()->updateExistingPivot($customer, ['is_grp_admin' => false]);

            if(! $result) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Organiser not found'],
                ], 404);
            }
        }

        $moment->members()->updateExistingPivot($customer, ['is_grp_admin' => true]);

        //Return the updated moment
        return response()->json([
            'success' => true,
            'message' => 'Admin status updated',
        ], 200);
    }

    public function size(Moment $moment){
        
        return response()->json([
            'size' => $moment->getOrganisers()->count()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Moment  $moment
     * @param  App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function removeOrganiser(Moment $moment, Customer $customer)
    {
        if($moment->members()
            ->wherePivot('is_organiser', true)->get()
            ->where('uuid', $customer->uuid)){

            $result = $moment->members()->detach($customer);

            if($result) { //If record was deleted
                return response()->json([
                    'success' => true,
                    'message' => 'Oraniser successfully removed'
                ], 200);
            }
        }
        
        return response()->json([
            'success' => false,
            'errors' => ['Organiser not found']
        ], 404);
    }
}
