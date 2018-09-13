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
        'category', 'title', 'date', 'time', 'location', 'icon', 'budget', 'is_memory'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'category', 'title', 'date', 'time', 'location', 'icon', 'budget', 'is_memory'
    ];

    protected $organisers = [];

    protected $guests = [];

	//Moment is created by Customer
	public function creator() { //does not work if foreign key is not specified
		return $this->belongsTo(Customer::class, 'customer_id');
	}

    public function members() {
        return $this->belongsToMany(Customer::class)->withPivot(['is_organiser', 'is_guest', 'is_grp_admin'])
                ->withTimestamps();
    }

    public function services() {

    	return $this->belongsToMany(Service::class);
    }

    public function chatGroup() {

    	return $this->hasOne(ChatGroup::class);
    }

    public function place() {
        return $this->hasOne(Place::class);
    }

    public function addOrganiser(Customer $customer) {

        $this->members->attach($customer, ['is_organiser' => true]);
    }

    public function addGuest(Customer $customer) {
        
        $this->members->attach($customer, ['is_guest' => true]);
    }

    public function getOrganisers() {

        $this->organisers = $this->members->where('is_organiser', true);

        return $this->organisers;
    }

    public function getGuests() {

        $this->guests = $this->members->where('is_guest', true);

        return $this->guests;
    }
}
