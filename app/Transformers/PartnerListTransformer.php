<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class PartnerListTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['breakdown'];

    public function transform($partner)
    {
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'address' => $partner->address,
            'logo' => $partner->logo,
            'original_price' => $partner->original_price,
            'discounted_price' => $partner->discounted_price,
            'discount' => $partner->discount,
            'delivery_charge' => $partner->delivery_charge
        ];
    }

    public function includeBreakdown($partner)
    {
        $collection = $this->collection($partner->breakdown, new BreakdownTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}