<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category', 'title',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'category', 'title',
    ];

	//Moment is created by Customer
	public function creator() {
		return $this->belongsTo(Customer::class);
	}

    public function members() {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }

    public function services() {

    	return $this->belongsToMany(Service::class);
    }

    public function chatGroup() {

    	return $this->hasOne(ChatGroup::class);
    }
}
