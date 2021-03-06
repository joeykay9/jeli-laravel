<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Moment extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category', 'title', 'icon', 'budget', 'is_memory', 'place_id',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [  
        'id', 'category', 'title', 'icon', 'budget', 'is_memory',
    ];

    protected $organisers = [];

    protected $guests = [];

	//Moment is created by Customer
	public function creator() { //does not work if foreign key is not specified
		return $this->belongsTo(Customer::class, 'customer_id');
	}

    public function members() {
        return $this->belongsToMany(Customer::class)
                ->withPivot(['is_organiser', 'is_grp_admin'])
                ->withTimestamps();
    }

    public function services() {

    	return $this->belongsToMany(Service::class);
    }

    public function place() {
        return $this->belongsTo(Place::class);
    }

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }

    public function addOrganiser(Customer $customer) {

        $this->members()->attach($customer, ['is_organiser' => true]);
    }

    public function addGuest(Customer $customer) {
        
        $this->members()->attach($customer, ['is_organiser' => false]);
    }

    public function getOrganisers() {

        $this->organisers = $this->members()->where('is_organiser', true)->get();

        return $this->organisers;
    }

    public function getGuests() {

        $this->guests = $this->members()->where('is_organiser', false);

        return $this->guests;
    }
}
