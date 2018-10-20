<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Customer;

class OneSignalDevice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'player_id', 'logged_in',
    ];

    public function user(){
    	return $this->belongsTo(Customer::class);
    }

    //Query Scopes
    public function scopeLoggedIn($query){
        return $query->where('logged_in', true)->get();
    }
}
