<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class PosServiceTransformer extends TransformerAbstract
{
    public function transform($service)
    {
        $service_discount = $service->discount();

        return [
            'name' => $service->name,
            'app_thumb' => $service->app_thumb,
            'price' => $service->price,
            'cost' => $service->cost,
            'category_id' => $service->subCategory->parent->id,
            'category_name' => $service->subCategory->parent->name,
            'sub_category_id' => $service->subCategory->id,
            'sub_category_name' => $service->subCategory->name,
            'stock_applicable' => !is_null($service->stock) ? true : false,
            'stock' => $service->stock,
            'vat_applicable' => $service->vat_percentage ? true : false,
            'vat' => $service->vat_percentage,
            'discount_id' => $service_discount ? $service_discount->id : null,
            'discount_amount' => $service_discount ? (double)$service_discount->amount : 0.00,
            'discount_applicable' => $service_discount ? true : false,
            'discounted_price' => $service_discount ? (double)$service->getDiscountedAmount() : 0,
            'discount_end_time' => $service_discount ? $service_discount->end_date->format('Y-m-d') : null
        ];
    }
}