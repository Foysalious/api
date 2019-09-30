<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class BusRouteTransformer extends TransformerAbstract
{
    public function transform($location)
    {
        return [
            'id' => $location->_id,
            'name' => $location->name
        ];
    }
}