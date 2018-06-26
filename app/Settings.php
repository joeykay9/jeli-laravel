<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'read_receipts', 'live_location',
    ];

    public function customer() {
    	return $this->belongsTo(Customer::class);
    }
}
