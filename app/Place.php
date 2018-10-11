<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
	protected $fillable = [
        'id', 'place_id', 'moment_id', 'place_name', 'place_image',
    ];

    protected $visible = [
        'place_id', 'moment_id', 'place_name', 'place_image',
    ];

    public function moment() {
    	return $this->belongsTo(Moment::class);
    }
}
