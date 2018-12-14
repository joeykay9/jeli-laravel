<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
	protected $fillable = [
		'place_id', 'place_name', 'place_image'
    ];

    protected $visible = [
        'place_id', 'place_name', 'place_image',
    ];

    public function moments() {
    	return $this->hasMany(Moment::class);
    }
}
