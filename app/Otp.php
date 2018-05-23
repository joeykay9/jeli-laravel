<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'otp'
    ];

    protected $otp;

    /**
     * OTP constructor.
     *
     */
    public function __construct()
    {
        $this->otp = $this->generateOTP();
        $this->fill(['otp' => $this->otp]);
    }

    protected function generateOTP() {
        return mt_rand(100000, 999999);
    }

    public function customer() {
    	return $this->belongsTo(Customer::class);
    }
}
