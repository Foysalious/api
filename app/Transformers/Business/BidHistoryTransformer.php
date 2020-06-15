<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class BidHistoryTransformer extends TransformerAbstract
{
    public function transform($bid)
    {
        $bidder = $bid->bidder;
        return [
            'id' => $bid->id,
            'service_provider' => [
                'name' => $bidder->name,
                'status' => $bidder->status,
                'rating' => number_format($bidder->reviews()->avg('rating'),1),
            ],
            'price' => $bid->price,
        ];
    }
}