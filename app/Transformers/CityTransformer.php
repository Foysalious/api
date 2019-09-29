<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class CityTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['locations'];

    public function transform($city)
    {
        return [
            'id' => $city->id,
            'name' => $city->name,
        ];
    }

    public function includeLocations($city)
    {
        $collection = $this->collection($city->locations, new LocationTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}