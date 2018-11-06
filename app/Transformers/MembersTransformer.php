<?php

namespace App\Transformers;

use App\Customer;
use League\Fractal\TransformerAbstract;

class MembersTransformer extends TransformerAbstract 
{
	/**
	* Turn this item object into a generic array
	*
	* @return array
	*/

	public function transform (Customer $member) {

		$user = auth('api')->user()
							->contacts()
							->where('contact_id', $member->id)
							->where('contact_id', '<>', auth('api')->user()->id)
							->first();

		return [
			'rid' => ($member->pivot->moment_id . $member->phone),
			'moment_id' => $member->pivot->moment_id,
			'contact_name' => (is_null($user) ? 'You' : $user->pivot->contact_name),
			'contact_phone' => $member->phone,
			'avatar' => $member->avatar,
			'is_organiser' => $member->pivot->is_organiser,
			'is_guest' => $member->pivot->is_guest,
		];
	}

}