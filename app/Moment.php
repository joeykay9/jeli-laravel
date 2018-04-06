<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
	//Moment is created by Customer
	public function creator() {
		return $this->belongsTo(Customer::class);
	}

    public function services() {

    	return $this->belongsToMany(Service::class);
    }

    public function chatGroup() {

    	return $this->hasOne(ChatGroup::class);
    }
}
