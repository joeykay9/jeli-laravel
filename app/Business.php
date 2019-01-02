<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Vendor;
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

    public function vendors(){
        
        return $this->hasMany(Vendor::class);
    }

    public function services() {

        return $this->hasMany(Service::class);
    }

<<<<<<< HEAD
    public function associates()
=======
    public function createVendor(Vendor $vendor){
        $this->vendors()->save($vendor);

        //Send new JeliVendor an email with login link
        \Mail::to($vendor)->send(new VendorWelcome($vendor));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
>>>>>>> parent of 3a83732... plenty jeli business steezes
    {
        $this->notify(new BusinessResetPasswordNotification($token));
    }
}
