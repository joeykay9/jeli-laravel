<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email', 'jelion', 'password', 'verified', 'active', 'otp',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'first_name', 'last_name', 'phone', 'email', 'jelion', 'verified', 'active'
    ];

    // public function getRouteKeyName(){
    //     return 'phone';
    // }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function routeNotificationForSMS()
    {
        return $this->phone;
    }

    //RELATIONSHIPS
    //Customer created
    public function createdMoments() {
        return $this->hasMany(Moment::class);
    }

    public function moments() {
        return $this->belongsToMany(Moment::class)->withTimestamps();
    }

    public function createMoment(Moment $moment){
        $this->createdMoments()->save($moment);
    }
}
