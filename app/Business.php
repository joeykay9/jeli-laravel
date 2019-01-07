<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Mail\Welcome;
use App\Notifications\BusinessResetPasswordNotification;

class Business extends Authenticatable
{
    use Notifiable;

    protected $guard = 'business';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'country', 'location', 'phone', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function services() {

        return $this->hasMany(Service::class);
    }

    public function associates() {

    }
    
}
