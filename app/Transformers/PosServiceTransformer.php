<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class PosServiceTransformer extends TransformerAbstract
{
    public function transform($service)
    {
        $service_discount = $service->discount();

        return [
            'id' => $service->id,
            'name' => $service->name,
            'app_thumb' => $service->app_thumb,
            'app_banner' => $service->app_banner,
            'thumb' => $service->thumb,
            'banner' => $service->banner,
            'weight' => $service->weight,
            'weight_unit' => $service->weight_unit ? array_merge(config('weight.weight_unit')[$service->weight_unit], ['key' => $service->weight_unit]) : null,
            'is_published_for_shop' => (int)$service->is_published_for_shop,
            'price' => $service->price,
            'wholesale_price' => $service->wholesale_price,
            'cost' => $service->cost,
            'category_id' => $service->subCategory->parent->id,
            'master_category_id' => $service->subCategory->parent->id,
            'category_name' => $service->subCategory->parent->name,
            'sub_category_id' => $service->subCategory->id,
            'sub_category_name' => $service->subCategory->name,
            'stock_applicable' => !is_null($service->stock) ? true : false,
            'stock' => $service->stock,
            'vat_applicable' => $service->vat_percentage ? true : false,
            'vat' => $service->vat_percentage,
            'unit' => $service->unit ? array_merge(constants('POS_SERVICE_UNITS')[$service->unit], ['key' => $service->unit]) : null,
            'description' => $service->description,
            'description_applicable' => $service->description ? true : false,
            'warranty_applicable' => $service->warranty ? true : false,
            'warranty' => (double)$service->warranty,
            'warranty_unit' => config('pos.warranty_unit')[$service->warranty_unit],
            'service_unit' => $service->unit,
            'unit_applicable' => $service->unit ? true : false,
            'discount_id' => $service_discount ? $service_discount->id : null,
            'discount_amount' => $service_discount ? (double)$service_discount->amount : 0.00,
            'discount_applicable' => $service_discount ? true : false,
            'discounted_price' => $service_discount ? (double)$service->getDiscountedAmount() : 0,
            'discount_percentage' => $service_discount ? $service->getDiscountPercentage() : 0,
            'discount_end_time' => $service_discount ? $service_discount->end_date->format('Y-m-d') : null,
            'product_link' => config('sheba.webstore_url') . '/' . $service->partner->sub_domain . '/product/' . $service->id,
            'show_image' => ($service->show_image) || is_null($service->show_image) ? 1 : 0,
            'shape' => $service->shape,
            'color' => $service->color,
            'image_gallery' => $service->imageGallery ? $service->imageGallery->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_link' => $image->image_link
                ];

            }) : [],
            'is_emi_available' => (bool)$service->is_emi_available
        ];
    }
}
