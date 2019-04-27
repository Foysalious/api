<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class PosServiceTransformer extends TransformerAbstract
{
    public function transform($service)
    {
        return [
            'name' => $service->name,
            'app_thumb' => $service->app_thumb,
            'price' => $service->price,
            'cost' => $service->cost,
            'category_id' => $service->subCategory->parent->id,
            'category_name' => $service->subCategory->parent->name,
            'sub_category_id' => $service->subCategory->id,
            'sub_category_name' => $service->subCategory->name,
            'stock_applicable' => $service->stock ? true : false,
            'stock' => $service->stock,
            'vat_applicable' => $service->vat_percentage ? true : false,
            'vat' => $service->vat_percentage,
            'discount_id' => $service->discount() ? $service->discount()->id : null,
            'discount_applicable' => $service->discount() ? true : false,
            'discounted_price' => $service->discount() ? $service->getDiscountedAmount() : 0,
            'discount_end_time' => $service->discount() ? $service->discount()->end_date->format('Y-m-d') : null
        ];
    }
}