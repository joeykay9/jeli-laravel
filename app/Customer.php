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
        'uuid', 'first_name', 'last_name', 'phone', 'email', 'jelion', 'dob', 'avatar', 'verified', 'active'
    ];

    /**
     * The claims decoded from the JWT token.
     *
     * @var array
     */
    
    private $claims;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    // public function __construct($claims)
    // {
    //     $this->claims = $claims;
    // }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    // public function getAuthIdentifierName()
    // {
    //     return 'sub';
    // }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return (string) $this->claims['sub'];
    }

    // /**
    //  * Get the password for the user.
    //  *
    //  * @return string
    //  */
    // public function getAuthPassword()
    // {
    //     throw new \Exception('No password for Firebase User');
    // }

    /*
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
     
    public function getRememberToken()
    {
        throw new \Exception('No remember token for Firebase User');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        throw new \Exception('No remember token for Firebase User');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        throw new \Exception('No remember token for Firebase User');
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

    public function getRouteKeyName(){
        return 'uuid';
    }
     

    public function routeNotificationForSMS()
    {
        return $this->phone;
    }

    public function routeNotificationForOneSignal()
    {
        return $this->devices()->loggedIn()->pluck('player_id')->toArray();
    }

    //RELATIONSHIPS
    //Customer created 
    public function createdMoments() {
        return $this->hasMany(Moment::class);
    }

    public function moments() {
        return $this->belongsToMany(Moment::class)
                ->withPivot('is_organiser', 'is_grp_admin')
                ->withTimestamps();
    }

    public function contacts() {
        return $this->belongsToMany(Customer::class, 'customer_contact', 'customer_id', 'contact_id')
                ->withPivot('contact_name')
                ->withTimestamps();
    }

    public function settings() {
        return $this->hasOne(Settings::class);
    }

    public function devices() {
        return $this->hasMany(OneSignalDevice::class);
    }

    public function createMoment(Moment $moment){
        $this->createdMoments()->save($moment);
    }

    //Query Scopes
    public function scopeActive($query){
        return $query->where('active', true)->get();
    }

    public function scopeInactive($query){
        return $query->where('active', false)->get();
    }

    public function getMutualMoments(Customer $customer)
    {
        $moments = $customer->moments()->get();

        return $this->moments()
                    ->whereIn('id', $moments->pluck('id'))
                    ->get();
    }
}
