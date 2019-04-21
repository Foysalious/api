<?php namespace App\Transformers;

use App\Models\PosOrderItem;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PosOrderItem $item)
    {
        return [
            'id'    => $item->service_id ? (int)$item->service_id : null,
            'name'  => $item->service_name,
            'price' => (double)$item->getTotal()
        ];
    }
}