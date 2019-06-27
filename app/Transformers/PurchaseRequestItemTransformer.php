<?php namespace App\Transformers;

use App\Models\PurchaseRequestItem;
use League\Fractal\TransformerAbstract;

class PurchaseRequestItemTransformer extends TransformerAbstract
{
    public function transform(PurchaseRequestItem $item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'result' => $item->result
        ];
    }
}