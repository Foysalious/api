<?php namespace App\Transformers;

use App\Models\PurchaseRequestItem;
use League\Fractal\TransformerAbstract;

class PurchaseRequestItemTransformer extends TransformerAbstract
{
    public function transform(PurchaseRequestItem $item)
    {
        $items = [];
        $item->fields->each(function ($field) use (&$items) {
            $items[] = [
                'title' => $field->title,
                'result' => $field->result
            ];
        });

        return $items;
    }
}