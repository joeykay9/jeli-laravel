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
			'full_name' => $contact->first_name . ' ' . $contact->last_name,
			'phone' => $contact->phone,
			'avatar' => $contact->avatar,
		];
	}
}