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
			'uuid' => $customer->uuid,
			'name' => $customer->pivot->contact_name,
			'phone' => $customer->phone,
			'avatar' => $customer->avatar,
		];
	}
}