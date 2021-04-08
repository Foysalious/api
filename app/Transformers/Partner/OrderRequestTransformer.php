<?php namespace App\Transformers\Partner;

use App\Jobs\Job;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        $diff_in_seconds = (Carbon::now()->diffInSeconds($request->created_at));
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
            'is_rent_a_car' => $job->isRentCar(),
            'rent_a_car_service_info' => $job->isRentCar() ? $this->formatServices($job->jobServices) : null,
            'pick_up_location' => $job->carRentalJobDetail && $job->carRentalJobDetail->pickUpLocation ? $job->carRentalJobDetail->pickUpLocation->name : null,
            'pick_up_address' => $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null,
            'pick_up_address_geo' => $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->pick_up_address_geo) : null,
            'destination_location' => $job->carRentalJobDetail && $job->carRentalJobDetail->destinationLocation ? $job->carRentalJobDetail->destinationLocation->name : null,
            'destination_address' => $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null,
            'destination_address_geo' => $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->destination_address_geo) : null,
            'total_price' => (double)$request->partnerOrder->calculate()->totalPrice,
            'status' => $request->status,
            'category_id' => $job->category ? $job->category->id : null,
            'number_of_order' => 1,
            'is_order_request' => true,
            'is_subscription_order' => false,
            'request_accept_time_limit_in_seconds' => config('partner.order.request_accept_time_limit_in_seconds'),
            'time_left_to_accept_in_seconds' => $diff_in_seconds <= config('partner.order.request_accept_time_limit_in_seconds') ? $diff_in_seconds : 0,
            'show_resource_list' => config('partner.order.show_resource_list')
        ];
    }

    /**
     * @param $job_services
     * @return Collection
     */
    private function formatServices($job_services)
    {
        $services = collect();
        foreach ($job_services as $job_service) {
            $services->push([
                'id' => $job_service->id,
                'service_id' => $job_service->service_id,
                'name' => $job_service->service->name,
                'image' => $job_service->service->app_thumb,
                'variables' => json_decode($job_service->variables),
                'unit' => $job_service->service->unit,
                'quantity' => $job_service->quantity
            ]);
        }
        return $services;
    }
}
