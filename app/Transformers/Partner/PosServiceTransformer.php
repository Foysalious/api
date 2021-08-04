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
            'app_thumb' => $pos_service->app_thumb,
            'original_price' => (double)$pos_service->price,
            'vat_included_price' => $pos_service->price + ($pos_service->price * $pos_service->vat_percentage) / 100,
            'vat_percentage' => (double)$pos_service->vat_percentage,
            'unit' => $pos_service->unit,
            'stock' => $pos_service->getStock(),
            'category_id' => $pos_service->subCategory->parent->id,
            'category_name' => $pos_service->subCategory->parent->name,
            'weight' => $pos_service->weight,
            'weight_unit'=>$pos_service->weight_unit,
            'discount_applicable' => $pos_service->discount() ? 1 : 0,
            'discounted_amount' => $pos_service->discount() ? $pos_service->getDiscountedAmount() : 0,
            'discount_percentage' => $pos_service->discount() ? $pos_service->getDiscountPercentage() : 0,
            'image_gallery' => $pos_service->imageGallery ? $pos_service->imageGallery->map(function($image){
                return [
                    'id' =>   $image->id,
                    'image_link' => $image->image_link
                ];
            }) : []
        ];
    }
}
