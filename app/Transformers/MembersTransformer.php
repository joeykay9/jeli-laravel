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

		return [
			'first_name' => $member->first_name,
			'last_name' => $member->last_name,
			'phone' => $member->phone,
			'avatar' => $member->avatar,
			'is_organiser' => $member->pivot->is_organiser,
			'is_guest' => $member->pivot->is_guest,
		];
	}

}