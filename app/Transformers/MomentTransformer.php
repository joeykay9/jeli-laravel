<?php

namespace App\Transformers;

use App\Moment;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class MomentTransformer extends TransformerAbstract
{

	protected $defaultIncludes = [
		'schedules',
		'members',
		'place'
	];

	public function transform (Moment $moment) {
		
		return [
			"id" => $moment->id,
            "category" => $moment->category,
            "title" => $moment->title,
            // "place_name" => $moment->place()->first()->place_name,
            "icon" => $moment->icon,
            "is_memory" => $moment->is_memory,
		];
	}

	public function includeSchedules(Moment $moment) {
        $schedules = $moment->schedules;

        return $this->collection($schedules, new ScheduleTransformer);
    }

    public function includeMembers(Moment $moment) {
    	$members = $moment->members;

    	return $this->collection($members, new MembersTransformer);
    }

    public function includePlace(Moment $moment) {
    	$place = $moment->place;

    	return $this->item($place, new PlaceTransformer);
    }
}