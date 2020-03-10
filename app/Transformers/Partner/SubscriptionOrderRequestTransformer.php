<?php namespace App\Transformers\Partner;

use App\Models\Category;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;

class SubscriptionOrderRequestTransformer extends TransformerAbstract
{
    /**
     * @param SubscriptionOrderRequest $request
     * @return array
     */
    public function transform(SubscriptionOrderRequest $request)
    {
        /** @var Category $category */
        $category = $request->subscriptionOrder->category;
        /** @var SubscriptionOrder $subscription_order */
        $subscription_order = $request->subscriptionOrder;
        $subscription_order->calculate();

        $schedules = json_decode($request->subscriptionOrder->schedules);
        $schedule_time = explode('-', $schedules[0]->time);

        return [
            'id'                    => $request->id,
            'subscription_order_id' => $subscription_order->id,
            'service_name'          => [
                'bn' => $category->bn_name ?: null,
                'en' => $category->name
            ],
            'address'               => $subscription_order->deliveryAddress->address,
            'location_name'         => $subscription_order->location->name,
            'created_at'            => $request->created_at->timestamp,
            'created_at_readable'   => $request->created_at->diffForHumans(),
            'created_date'          => $request->created_at->format('Y-m-d'),
            'schedule_date'         => $schedules[0]->date,
            'schedule_time_start'   => $schedule_time[0],
            'schedule_time_end'     => $schedule_time[1],
            'schedule_at'           => Carbon::parse($schedules[0]->date .' '. $schedule_time[0])->timestamp,
            'schedules'             => $subscription_order->getScheduleDates(),
            'created_time'          => $request->created_at->format('h:m:s A'),
            'total_price'           => (double)$subscription_order->getTotalPrice(),
            'status'                => $request->status,
            'number_of_order'       => count($schedules),
            'is_order_request'      => true,
            'is_subscription_order' => true,
            'created_date_start'    => $schedules[0]->date,
            'created_date_end'      => end($schedules)->date,
            'request_accept_time_limit_in_seconds' => config('partner.order.request_accept_time_limit_in_seconds'),
            'show_resource_list' => config('partner.order.show_resource_list')
        ];
    }
}
