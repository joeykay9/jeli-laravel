<?php

namespace App\Transformers;

use App\Customer;
use League\Fractal\TransformerAbstract;

/**
 * 
 */
class MomentTransformer extends TransformerAbstract
{
	
	function __construct(argument)
	{
		
	}

	public function transform(Moment $moment) {
		
		return [
			
		];
	}
}