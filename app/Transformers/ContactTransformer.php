<?php

namespace App\Transformers;

use App\Customer;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class ContactTransformer extends TransformerAbstract
{
	public function transform (Customer $contact) {

		return [
			'uuid' => $contact->uuid,
			'name' => $contact->pivot->contact_name,
			'phone' => $contact->phone,
			'avatar' => $contact->avatar,
		];
	}
}