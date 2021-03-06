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
							//->where('contact_id', '<>', auth('api')->user()->id)
							->first();
		$myself = ($member->id == auth('api')->user()->id);

		return [
			'uuid' => $member->uuid,
			'contact_name' => (is_null($user) 
							? (
								($myself) 
								? 'You' 
								: ('~' . $member->jelion)
							) : $user->pivot->contact_name),
			'contact_phone' => $member->phone,
			'avatar' => $member->avatar,
			'is_organiser' => $member->pivot->is_organiser,
		];
	}

}