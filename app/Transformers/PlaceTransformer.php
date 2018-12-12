<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Place;

class PlaceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Place $place)
    {
        return [
            'place_id' => $place->place_id,
            'place_name' => $place->place_name
        ];
    }
}
