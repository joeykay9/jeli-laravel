<?php

namespace App\Transformers;

use App\Contact;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class ContactTransformer extends TransformerAbstract
{
	public function transform (Contact $contact) {

		return [
			'uuid' => $contact->uuid,
			'name' => $contact->name,
			'phone' => $contact->phone,
			'avatar' => $contact->avatar,
		];
	}
}