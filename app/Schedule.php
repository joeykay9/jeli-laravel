<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moment_schedules';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'start_date', 'end_date', 'start_time', 'end_time', 'moment_id',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'start_date', 'end_date', 'start_time', 'end_time',
    ];


    public function moment() {
    	return $this->belongsTo(Moment::class);
    }
}
