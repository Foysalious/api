<?php namespace App\Transformers\Partner;


use League\Fractal\TransformerAbstract;

class PosServiceTransformer extends TransformerAbstract
{

    public function transform($pos_service)
    {
        return [
            'id' => $pos_service->id,
            'name' => $pos_service->name,
            'thumb' => $pos_service->thumb,
            'original_price' => (double)$pos_service->price,
            'vat_included_price' => $pos_service->price + ($pos_service->price * $pos_service->vat_percentage) / 100,
            'vat_percentage' => (double)$pos_service->vat_percentage,
            'unit' => $pos_service->unit,
            'stock' => (double)$pos_service->stock,
            'category_id' => $pos_service->pos_category_id
        ];
    }

}