<?php

namespace App\Transformers;

use App\Customer;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class CustomerTransformer extends TransformerAbstract
{
	
	function __construct(argument)
	{
		# code...
	}

	public function transform(Customer $customer) {
		
		return [

		];
	}
}