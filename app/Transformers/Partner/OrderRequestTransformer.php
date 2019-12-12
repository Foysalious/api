<?php namespace App\Transformers\Partner;

use App\Models\Order;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;

class OrderRequestTransformer extends TransformerAbstract
{
    /**
     * @param PartnerOrderRequest $request
     * @return array
     */
    public function transform(PartnerOrderRequest $request)
    {
        $category = $request->partnerOrder->lastJob()->category;
        /** @var Order $order */
        $order = $request->partnerOrder->order;

        return [
            'id' => $request->id,
            'service_name' => [
                'bn' => $category->bn_name ?: null,
                'en' => $category->name
            ],
            'address'       => $order->deliveryAddress->address,
            'location_name' => $order->location->name,
            'created_at'    => $request->created_at->timestamp,
            'created_at_readable' => $request->created_at->diffForHumans(),
            'created_date'  => $request->created_at->format('Y-m-d'),
            'created_time'  => $request->created_at->format('h:m:s A'),
            'price'         => (double)$request->partnerOrder->calculate()->totalPrice,
            'status'        => $request->status,
            'number_of_order' => $request->partnerOrder->order->subscription ? $this->getNumberOfSubscriptionOrder($request) : 1,
            'is_order_request'=> true,
            'is_subscription_order'=> $request->partnerOrder->order->subscription ? true : false
        ];
    }

    private function getNumberOfSubscriptionOrder(PartnerOrderRequest $request)
    {
        return $request->partnerOrder->order->subscription->orders->count();
    }
}
