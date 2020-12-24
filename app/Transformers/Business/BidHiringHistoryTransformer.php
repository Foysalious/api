<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Business\Bid\HiringHistoryStatusCalculator;

class BidHiringHistoryTransformer extends TransformerAbstract
{
    public function transform($bid)
    {
        $bidder = $bid->bidder;
        return [
            'id' => $bid->id,
            'service_provider' => [
                'id' => $bidder->id,
                'name' => $bidder->name,
                'image' => $bid->bidder->logo,
            ],
            'created_at' => $bid->created_at->format('h:i a') . ',' . $bid->created_at->format('d F Y'),
            'status' => HiringHistoryStatusCalculator::resolveStatus($bid),
            'quotation_price' => $bid->price,
            'requested_price' => $bid->bidder_price > 0 ? $bid->bidder_price : $bid->price,
        ];
    }
}