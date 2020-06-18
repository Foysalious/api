<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Business\Procurement\OrderStatusCalculator;

class ProcurementOrderTransformer extends TransformerAbstract
{
    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $bid = $procurement->getActiveBid() ? $procurement->getActiveBid() : null;
        return [
            'id' => $procurement->id,
            "title" => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'status' => OrderStatusCalculator::resolveStatus($procurement),
            'created_at' => $procurement->created_at->format('d/m/y'),
            'bid' => [
                'id' => $bid ? $bid->id : null,
                'price' => $bid ? $bid->price : null,
                'service_provider' => [
                    'id' => $bid ? $bid->bidder->id : null,
                    'name' => $bid ? $bid->bidder->name : null,
                    'image' => $bid ? $bid->bidder->getContactResourceProPic() : null
                ]
            ]
        ];
    }
}