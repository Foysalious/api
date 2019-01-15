<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class TimeTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'slots'
    ];

    public function transform($time)
    {
        return [
            'date' => $time['value'],
        ];
    }

    public function includeSlots($time)
    {
        return $this->collection($time['slots'], new SlotTransformer());
    }
}