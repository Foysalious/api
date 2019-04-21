<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform($item)
    {
        return [
            'id'    => (int)$item->service_id,
            'name'  => $item->service_name,
            'price' => (double)$item->total
        ];
    }
}