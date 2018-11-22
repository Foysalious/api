<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class PartnerListTransformer extends TransformerAbstract
{
    public function transform($partner)
    {
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'address' => $partner->address
        ];
    }
}