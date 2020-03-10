<?php namespace App\Transformers\Partner;

use App\Jobs\Job;
use App\Models\Order;
use Carbon\Carbon;
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
        $job = $request->partnerOrder->lastJob();
        $category = $job->category;
        /** @var Order $order */
        $order = $request->partnerOrder->order;
        /** @var Job $job */
        $job = $order->lastJob();
        $diff_in_seconds = (Carbon::now()->diffInSeconds($request->created_at)) - 5;
        return [
            'id' => $request->id,
            'job_id' => $job->id,
            'code' => $order->code(),
            'partner_order_id' => $request->partner_order_id,
            'service_name' => [
                'bn' => $category->bn_name ?: null,
                'en' => $category->name
            ],
            'address' => $order->deliveryAddress->address,
            'location_name' => $order->location_id ? $order->location->name : $order->deliveryAddress->address,
            'created_at' => $request->created_at->timestamp,
            'created_at_readable' => $request->created_at->diffForHumans(),
            'created_date' => $request->created_at->format('Y-m-d'),
            'schedule_date' => $job->schedule_date,
            'schedule_time_start' => $job->preferred_time_start,
            'schedule_time_end' => $job->preferred_time_end,
            'schedule_at' => Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_end)->timestamp,
            'created_time' => $request->created_at->format('g:i:s A'),
            'total_price' => (double)$request->partnerOrder->calculate()->totalPrice,
            'status' => $request->status,
            'number_of_order' => 1,
            'is_order_request' => true,
            'is_subscription_order' => false,
            'request_accept_time_limit_in_seconds' => config('partner.order.request_accept_time_limit_in_seconds'),
            'time_left_to_accept_in_seconds' => $diff_in_seconds <= config('partner.order.request_accept_time_limit_in_seconds') ? $diff_in_seconds : 0,
            'show_resource_list' => config('partner.order.show_resource_list')
        ];
    }
}
