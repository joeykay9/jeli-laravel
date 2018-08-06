<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Customer;
use App\Moment;

class ChatGroup extends Model
{

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'size',
    ];
    
    public function moment() {

        return $this->belongsTo(Moment::class);
    }

    public function addMember(Customer $customer, Moment $moment){
    	# Logic
    	// 1. Get customer_moment entry

    	// 2a. If exists, check is_organizer flag
    		// 2a-i. If false, flag as true
    		// 2a-i. Increase group size by 1
    		// 2a-i. Return success response
    		
    		// 2a-ii. If true, return already a member response
    	
    	// 2b. If null, return failure response
    }

    public function makeAdmin(Customer $customer, Moment $moment) {

    }
}
