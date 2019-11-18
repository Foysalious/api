<?php namespace App\Transformers\Partner;

use App\Models\PartnerOrderRequest;
use League\Fractal\TransformerAbstract;

class OrderRequestTransformer extends TransformerAbstract
{
    /**
     * @param PartnerOrderRequest $request
     * @return array
     */
    public function transform(PartnerOrderRequest $request)
    {
        $category = $request->partnerOrder->lastJob()->category;
        return [
            'id' => $request->id,
            'service_name' => [
                'bn' => $category->bn_name ?: null,
                'en' => $category->name
            ],
            'created_date' => $request->created_at->format('Y-m-d'),
            'created_time' => $request->created_at->format('h:m:s A'),
            'price' => $request->partnerOrder->calculate()->totalPrice,
            'status' => $request->status
        ];
    }
}
