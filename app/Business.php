<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use App\Mail\Welcome;
use App\Notifications\BusinessResetPasswordNotification;

class Business extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'category', 'country', 'location', 'phone', 'email', 'password',
    ];

    public function owner(){
        
        return $this->belongsTo(Customer::class);
    }

    public function services() {

        return $this->hasMany(Service::class);
    }

    public function associates()
    {
        return $this->belongsToMany(Customer::class)
                ->withPivot('role')
                ->withTimestamps();
    }
}
