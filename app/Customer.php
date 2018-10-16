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
        'uuid', 'first_name', 'last_name', 'phone', 'email', 'jelion', 'dob', 'avatar', 'password', 'active',
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
        'uuid', 'first_name', 'last_name', 'phone', 'email', 'jelion', 'dob', 'avatar', 'active'
    ];

    public function getRouteKeyName(){
        return 'uuid';
    }

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

    public function routeNotificationForOneSignal()
    {
        return 'ONE_SIGNAL_PLAYER_ID';
    }

    //RELATIONSHIPS
    //Customer created
    public function createdMoments() {
        return $this->hasMany(Moment::class);
    }

    public function moments() {
        return $this->belongsToMany(Moment::class)
                ->withPivot('is_organiser', 'is_guest', 'is_grp_admin')
                ->withTimestamps();
    }

    public function otp() {
        return $this->hasOne(Otp::class);
    }

    public function settings() {
        return $this->hasOne(Settings::class);
    }

    public function createMoment(Moment $moment){
        $this->createdMoments()->save($moment);
    }

    public function scopeActive($query){
        return $query->where('active', true)->get();
    }

    public function scopeInactive($query){
        return $query->where('active', false)->get();
    }
}
