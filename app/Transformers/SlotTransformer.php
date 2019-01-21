<?php


namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class SlotTransformer extends TransformerAbstract
{
    public function transform($slot)
    {
        return [
            'key' => $slot['key'],
            'value' => $slot['value']
        ];
    }
}