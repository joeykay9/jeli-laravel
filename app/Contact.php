<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'name', 'phone', 'avatar',
    ];

    public function customer() {
    	return $this->belongsTo(Customer::class);
    }
}
