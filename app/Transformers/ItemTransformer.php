<?php namespace App\Transformers;

use App\Models\PosOrderItem;
use League\Fractal\TransformerAbstract;

class ItemTransformer extends TransformerAbstract
{
    public function transform(PosOrderItem $item)
    {
        $default_item_service_app_thumb = config('s3.url') . "images/pos/services/thumbs/quick_sale_82_82.png";
        return [
            'id'                => $item->service_id ? (int)$item->service_id : null,
            'item_id'           => $item->id,
            'name'              => $item->service_name,
            'quantity'          => $item->quantity,
            'unit_price'        => (double)$item->unit_price,
            'app_thumb'         => $item->service ? $item->service->app_thumb : $default_item_service_app_thumb,
            'price'             => (double)$item->getTotal(),
            'price_without_vat' => (double)$item->getTotal() - $item->getVat(),
            'discount_amount'   => (double)$item->getDiscountAmount(),
            'vat_percentage'    => $item->service ? (double)$item->service->vat_percentage : 0.00,
            'warranty'          => $item->service ? (double)$item->service->warranty : 0.00,
            'warranty_unit'     => $item->service ? config('pos.warranty_unit')[$item->service->warranty_unit] : null,
            'image_gallery'     => $item->service && $item->service->imageGallery ? $item->service->imageGallery->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_link' => $image->image_link
                ];

            }) : []
        ];
    }
}
