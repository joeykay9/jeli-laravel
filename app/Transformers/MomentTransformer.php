<?php

namespace App\Transformers;

use App\Moment;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class MomentTransformer extends TransformerAbstract
{

	public function transform(Moment $moment) {

		// $schedule = DB::table('moment_schedules')
		// 				->where('moment_id', $moment->id)->get();
		
		return [
			'id' => $moment->id,
			'title' => $moment->title,
			'icon' => $moment->icon,
			'place_name' => $moment->place()->first()->place_name,
		];
	}
}