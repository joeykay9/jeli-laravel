<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public function moments() {
    	
    	return $this->belongsToMany(Moment::class);
    }
}
