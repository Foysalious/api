<?php namespace App\Transformers\Partner;

use App\Jobs\Job;
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
        /** @var Job $job */
        $job = $order->lastJob();

        $data = [
            'id'                    => $request->id,
            'service_name'          => [
                'bn' => $category->bn_name ?: null,
                'en' => $category->name
            ],
            'address'               => $order->deliveryAddress->address,
            'location_name'         => $order->location->name,
            'created_at'            => $request->created_at->timestamp,
            'created_at_readable'   => $request->created_at->diffForHumans(),
            'created_date'          => $request->created_at->format('Y-m-d'),
            'schedule_date'         => $job->schedule_date,
            'schedule_time_start'   => $job->preferred_time_start,
            'schedule_time_end'     => $job->preferred_time_end,
            'created_time'          => $request->created_at->format('h:m:s A'),
            'total_price'           => (double)$request->partnerOrder->calculate()->totalPrice,
            'status'                => $request->status,
            'number_of_order'       => $request->partnerOrder->order->subscription ? $this->getNumberOfSubscriptionOrder($request) : 1,
            'is_order_request'      => true,
            'is_subscription_order' => $request->partnerOrder->order->subscription ? true : false
        ];

        if ($request->partnerOrder->order->subscription) {
            $schedules = json_decode($request->partnerOrder->order->subscription->schedules, true);
            $data['created_date_start'] = $schedules[0]['date'];
            $data['created_date_end']   = end($schedules)['date'];
        }
        return $data;
    }

    private function getNumberOfSubscriptionOrder(PartnerOrderRequest $request)
    {
        return $request->partnerOrder->order->subscription->orders->count();
    }
}
