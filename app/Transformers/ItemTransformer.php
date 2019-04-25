<?php namespace App\Transformers;

use App\Models\PosOrderItem;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PosOrderItem $item)
    {
        return [
            'id'        => $item->service_id ? (int)$item->service_id : null,
            'name'      => $item->service_name,
            'quantity'  => $item->quantity,
            'price'     => (double)$item->getTotal(),
            'price_without_vat' =>  (double) $item->getTotal() - $item->getVat()
        ];
    }
}