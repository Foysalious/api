<?php namespace App\Repositories;

use App\Models\Job;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Jobs\JobStatuses;

class PartnerOrderRepository
{
    /** @var PartnerJobRepository */
    private $partnerJobRepository;

    public function __construct()
    {
        $this->partnerJobRepository = new PartnerJobRepository();
    }

    public function getOrderDetails($request)
    {
        $partner_order = $this->getInfo($this->loadAllRelatedRelations($request->partner_order));
        $jobs = $partner_order->jobs->whereIn('status', $this->getStatusFromRequest($request))->each(function ($job) use ($partner_order) {
            $job['partner_order'] = $partner_order;
            $job = $this->partnerJobRepository->getJobInfo($job);
            removeRelationsAndFields($job);
            array_forget($job, 'partner_order');
        })->values()->all();
        removeRelationsAndFields($partner_order);
        $partner_order['jobs'] = $jobs;
        return $partner_order;
    }

    public function getOrderDetailsV2($request)
    {
        $partner_order = $this->getInfo($this->loadAllRelatedRelationsV2($request->partner_order));

        $partner_order['is_logistic'] = $partner_order->order->isLogisticOrder();
        $partner_order['is_ready_to_pick'] = $partner_order->order->isReadyToPick();

        $partner_order['can_process'] = $partner_order->order->isProcessable();
        $partner_order['can_serve'] = $partner_order->order->isServeable();
        $partner_order['can_pay'] = $partner_order->order->isPayable();
        $jobs = $partner_order->jobs->filter(function (Job $job) {
            return $job->status !== JobStatuses::CANCELLED;
        })->each(function (Job $job) use ($partner_order) {
            $job['partner_order'] = $partner_order;
            $job = $this->partnerJobRepository->getJobInfo($job);
            $services = [];
            $job->jobServices->each(function ($job_service) use (&$services, $job) {
                $info = $this->partnerJobRepository->getJobServiceInfo($job_service);
                $info['name'] = $job_service->formatServiceName($job);
                $info['unit'] = $job_service->service->unit;
                $info['discount'] = (double)$job_service->discount;
                $info['sheba_contribution'] = (double)$job_service->sheba_contribution;
                $info['partner_contribution'] = (double)$job_service->partner_contribution;
                $info['sheba_contribution_amount'] = round(($info['discount'] * $info['sheba_contribution']) / 100, 2);
                $info['partner_contribution_amount'] = round(($info['discount'] * $info['partner_contribution']) / 100, 2);
                array_push($services, $info);
            });

            $job['category_name'] = $job->category ? $job->category->name : null;
            $job['complains'] = app('Sheba\Dal\Complain\EloquentImplementation')->jobWiseComplainInfo($job->id);

            if (!$job['complains']->isEmpty()) {
                $order = $job->partnerOrder->order;
                $complain_additional_info = [
                    'order_code' => $order->code(),
                    'order_id' => $order->id,
                    'customer_name' => $order->customer->profile->name,
                    'customer_profile_picture' => $order->customer->profile->pro_pic,
                    'schedule_date_and_time' => humanReadableShebaTime($job->preferred_time) . ', ' . Carbon::parse($job->schedule_date)->toFormattedDateString(),
                    'category' => $job->category->name,
                    'location' => $order->location->name,
                    'resource' => $job->resource ? $job->resource->profile->name : 'N/A',
                ];

                foreach ($job['complains'] as $key => $complain) {
                    $complain_additional_info['created_at'] = $complain['created_at']->format('jS F, Y');
                    $job['complains'][$key] = array_merge($complain, $complain_additional_info);
                }
            }
            removeRelationsAndFields($job);
            $job['services'] = $services;
            $job['preferred_time'] = humanReadableShebaTime($job->preferred_time);
            $job['pick_up_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null;
            $job['destination_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null;
            $job['drop_off_date'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y') : null;
            $job['drop_off_time'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A') : null;
            $job['estimated_distance'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null;
            $job['estimated_time'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null;

            array_forget($job, ['partner_order', 'carRentalJobDetail']);
            if ($job->isLogisticCreated()) {
                $job['logistic'] = $job->getCurrentLogisticOrder()->formatForPartner();
            }

        })->sortByDesc('id')->values()->all();
        removeRelationsAndFields($partner_order);
        $partner_order['jobs'] = $jobs;

        return $partner_order;
    }

    /**
     * @param $request
     * @return array
     */
    public function getNewOrdersWithJobs($request)
    {
        list($offset, $limit) = calculatePagination($request);
        $jobs = (new PartnerRepository($request->partner))->jobs(JobStatuses::getAcceptable(), $offset, $limit);

        $all_partner_orders = collect();
        $all_jobs = collect();

        $partner = $request->partner;
        $subscription_orders = $partner->subscriptionOrders->where('status', 'converted');
        foreach ($subscription_orders as $subscription_order) {
            $schedules = collect(json_decode($subscription_order->schedules));
            $service_details = json_decode($subscription_order->service_details);
            $service_details_breakdown = $service_details->breakdown['0'];
            $services = collect();
            $services->push([
                'name' => $service_details_breakdown->name,
                'quantity' => (double)$service_details_breakdown->quantity
            ]);

            $schedule_time = explode('-', $schedules->first()->time);
            $subscription = collect([
                'id' => $subscription_order->id,
                'customer_name' => $subscription_order->customer ? $subscription_order->customer->profile->name : '',
                'address' => $subscription_order->deliveryAddress ? $subscription_order->deliveryAddress->address : '',
                'location_name' => $subscription_order->location->name,
                'billing_cycle' => $subscription_order->billing_cycle,
                'total_orders' => $subscription_order->orders->count(),
                'original_price' => $service_details->original_price,
                'discount' => $service_details->discount,
                'total_price' => $service_details->discounted_price,
                'created_at' => $subscription_order->created_at->timestamp,
                'created_date_start' => $schedules->first()->date,
                'created_date_end' => $schedules->last()->date,
                "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                "preferred_time" => $schedules->first()->time,
                'category_name' => $subscription_order->category->name,
                'category_id' => $subscription_order->category->id,
                'service_name' => [
                    'bn' => $subscription_order->category->bn_name,
                    'en' => $subscription_order->category->name
                ],
                'services' => $services,
                'is_order_request' => false,
                'is_subscription_order' => true,
                'schedule_time_start' => Carbon::parse($schedule_time[0])->format('H:i:s'),
                'schedule_time_end' => Carbon::parse($schedule_time[1])->format('H:i:s'),
                'schedule_at' => Carbon::parse($schedules->first()->date . ' ' . explode('-', $schedules->first()->time)[0])->timestamp,
                'schedules' => $subscription_order->getScheduleDates()
            ]);
            $all_partner_orders->push($subscription);
        }

        foreach ($jobs->groupBy('partner_order_id') as $jobs) {
            if ($jobs[0]->partner_order->order->subscription_order_id == null) {
                $jobs[0]->partner_order->calculate(true);
                if ($jobs[0]->cancelRequests->where('status', 'Pending')->count() > 0) continue;
                $job = $jobs[0];
                $services = collect();
                if (count($job->jobServices) == 0) {
                    $variables = json_decode($job->service_variables);
                    $services->push(
                        [
                            'name' => $job->service_name,
                            'variables' => $variables,
                            'quantity' => (double)$job->quantity
                        ]
                    );
                } else {
                    foreach ($job->jobServices as $jobService) {
                        $variables = json_decode($jobService->variables);
                        $services->push([
                            'name' => $jobService->service->name,
                            'variables' => $variables,
                            'quantity' => (int)$jobService->quantity // TODO: FORCEFULLY CONVERTED DOUBLE TO INTEGER. REMOVE LATER
                        ]);
                    }
                }

                if (!$jobs[0]->partner_order->order->deliveryAddress) {
                    $jobs[0]->partner_order->order->deliveryAddress = $jobs[0]->partner_order->order->getTempAddress();
                }

                $job_preferred_time = explode('-', $jobs[0]->preferred_time);
                $order = collect([
                    'customer_name' => $jobs[0]->partner_order->order->deliveryAddress->name,
                    'address' => $jobs[0]->partner_order->order->deliveryAddress->address,
                    'location_name' => $jobs[0]->partner_order->order->location ? $jobs[0]->partner_order->order->location->name : $jobs[0]->partner_order->order->deliveryAddress->address,
                    'created_at' => $jobs[0]->partner_order->created_at->timestamp,
                    'created_at_readable' => $jobs[0]->partner_order->created_at->diffForHumans(),
                    'created_date' => $jobs[0]->partner_order->created_at->format('Y-m-d'),
                    'code' => $jobs[0]->partner_order->code(),
                    'is_on_premise' => $jobs[0]->site == 'partner' ? 1 : 0,
                    'id' => $jobs[0]->partner_order->id,
                    'total_price' => (double)$jobs[0]->partner_order->totalPrice,
                    'discount' => (double)$jobs[0]->partner_order->totalDiscount,
                    'category_name' => $jobs[0]->category ? $jobs[0]->category->name : null,
                    'category_id' => $jobs[0]->category ? $jobs[0]->category->id : null,
                    'service_name' => [
                        'bn' => $jobs[0]->category ? $jobs[0]->category->bn_name : null,
                        'en' => $jobs[0]->category ? $jobs[0]->category->name : null,
                    ],
                    'job_id' => $jobs[0]->id,
                    'schedule_date' => $jobs[0]->schedule_date,
                    'preferred_time' => $jobs[0]->readable_preferred_time,
                    'services' => $services,
                    'is_rent_a_car' => $jobs[0]->isRentCar(),
                    'rent_a_car_service_info' => $jobs[0]->isRentCar() ? $this->formatServices($jobs[0]->jobServices) : null,
                    'pick_up_location' => $jobs[0]->carRentalJobDetail && $jobs[0]->carRentalJobDetail->pickUpLocation ? $jobs[0]->carRentalJobDetail->pickUpLocation->name : null,
                    'pick_up_address' => $jobs[0]->carRentalJobDetail ? $jobs[0]->carRentalJobDetail->pick_up_address : null,
                    'pick_up_address_geo' => $jobs[0]->carRentalJobDetail ? json_decode($jobs[0]->carRentalJobDetail->pick_up_address_geo) : null,
                    'destination_location' => $jobs[0]->carRentalJobDetail && $jobs[0]->carRentalJobDetail->destinationLocation ? $jobs[0]->carRentalJobDetail->destinationLocation->name : null,
                    'destination_address' => $jobs[0]->carRentalJobDetail ? $jobs[0]->carRentalJobDetail->destination_address : null,
                    'destination_address_geo' => $jobs[0]->carRentalJobDetail ? json_decode($jobs[0]->carRentalJobDetail->destination_address_geo) : null,
                    'status' => $jobs[0]->status,
                    'is_order_request' => false,
                    'is_subscription_order' => $jobs[0]->partner_order->order->subscription ? true : false,
                    'schedule_time_start' => $job_preferred_time[0],
                    'schedule_time_end' => $job_preferred_time[1],
                    'schedule_at' => Carbon::parse($jobs[0]->schedule_date . ' ' . $job_preferred_time[0])->timestamp,
                    'request_accept_time_limit_in_seconds' => config('partner.order.request_accept_time_limit_in_seconds'),
                    'show_resource_list' => config('partner.order.show_resource_list')
                ]);

                $all_partner_orders->push($order);
            }
        }
        list($field, $orderBy) = $this->getSortByFieldAdOrderFromRequest($request);

        $orderBy = $orderBy == 'asc' ? 'sortBy' : 'sortByDesc';
        list($offset, $limit) = calculatePagination($request);
        return array_slice($this->partnerOrdersSortBy($field, $orderBy, $all_partner_orders, $all_jobs)->toArray(), $offset, $limit);
    }

    /**
     * @param $request
     * @return array
     */
    public function getOrders($request)
    {
        list($field, $orderBy) = $this->getSortByFieldAdOrderFromRequest($request);
        list($offset, $limit) = calculatePagination($request);
        $for = $request->for;

        $filter = $request->filter;
        $partner = $request->partner->load(['partner_orders' => function ($q) use ($filter, $orderBy, $field) {
            $q->$filter()->orderBy($field, $orderBy)->with(['jobs' => function ($q) {
                $q->with('usedMaterials', 'jobServices', 'category', 'resource.profile', 'review');
            }, 'order' => function ($q) {
                $q->with(['customer.profile', 'location']);
            }]);
        }]);
        $partner_orders = $this->filterEshopOrders($partner->partner_orders, $for);

        return array_slice($partner_orders->each(function ($partner_order) {
            $partner_order['version'] = $partner_order->is_v2 ? 'v2' : 'v1';
            /** @var Job $job */
            $job = $partner_order->jobs[0];
            if ($job->isLogisticCreated()) {
                $partner_order['logistic'] = $job->getCurrentLogisticOrder()->formatForPartner();
            }
            $partner_order['category_name'] = $partner_order->jobs[0]->category ? $partner_order->jobs[0]->category->name : null;
            removeRelationsAndFields($this->getInfo($partner_order));
        })->reject(function ($item, $key) {
            return $item->order_status == 'Open';
        })->values()->all(), $offset, $limit);
    }

    /**
     * @param $partner_orders
     * @param $for
     * @return mixed
     */
    private function filterEshopOrders($partner_orders, $for)
    {
        return $partner_orders->filter(function ($partner_order) use ($for) {
            if ($for == 'eshop') return !is_null($partner_order->order->partner_id);
            else return is_null($partner_order->order->partner_id);
        });
    }

    /**
     * @param $partner
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    public function getOrdersByClosedAt($partner, $start_time, $end_time)
    {
        return PartnerOrder::with('order.location', 'jobs.usedMaterials')
            ->where('partner_id', $partner->id)
            ->whereBetween('closed_at', [$start_time, $end_time])
            ->select('id', 'partner_id', 'order_id', 'closed_at', 'sheba_collection', 'partner_collection', 'finance_collection')
            ->get()->each(function ($partner_order) {
                $partner_order['sales'] = (double)$partner_order->calculate($price_only = true)->totalCost;
                $partner_order['week_name'] = $partner_order->closed_at->format('D');
                $partner_order['day'] = $partner_order->closed_at->toDateString();
                $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
                $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
                $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
                $partner_order['code'] = $partner_order->code();
                removeRelationsFromModel($partner_order);
            });
    }

    /**
     * @param $request
     * @return array
     */
    private function getSortByFieldAdOrderFromRequest($request)
    {
        $orderBy = 'desc';
        $field = 'created_at';
        if ($request->has('sort')) {
            $explode = explode(':', $request->get('sort'));
            $field = $explode[0];
            if (isset($explode[1]) && $explode[1] == 'asc') {
                $orderBy = 'asc';
            }
        }
        return [$field, $orderBy];
    }

    /**
     * @param $partner_order
     * @return mixed
     */
    private function loadAllRelatedRelations($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->orderBy('schedule_date')->with(['usedMaterials', 'resource.profile', 'review' => function ($q) {
                $q->with('customer.profile');
            }]);
        }]);
    }

    private function loadAllRelatedRelationsV2($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->with(['usedMaterials', 'carRentalJobDetail', 'resource.profile', 'jobServices' => function ($q) {
                $q->with('service');
            }]);
        }]);
    }

    public function getWeeklyBreakdown($partner_orders, $start_time, $end_time)
    {
        $week = collect();
        if (count($partner_orders) > 0) {
            $partner_orders->groupBy('day')->each(function ($item, $key) use ($week) {
                $week[Carbon::parse($key)->format('D')] = $item->sum('sales');
            });
        }
        for ($date = $start_time; $date < $end_time; $date->addDay()) {
            $day = $date->format('D');
            if (!isset($week[$day])) {
                $week->put($day, 0);
            }
        }
        return $week;
    }

    public function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        } else {
            return JobStatuses::getActuals();
        }
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') return JobStatuses::getOngoing();

        if ($filter == 'history') return JobStatuses::getActuals();
    }

    public function getInfo($partner_order)
    {
        if ($partner_order->jobs->count() > 1) {
            $job = $partner_order->lastJob();
        } else {
            $job = $partner_order->jobs->first();
        }
        if (!$partner_order->order->deliveryAddress) $partner_order->order->deliveryAddress = $partner_order->order->getTempAddress();
        $partner_order->calculate(true);
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->deliveryAddress->name;
        $partner_order['customer_mobile'] = $partner_order->order->isFromOfflineBondhu() ?
            $partner_order->order->affiliation->affiliate->profile->mobile :
            $partner_order->order->deliveryAddress->mobile;
        $partner_order['resource_picture'] = $job && $job->resource ? $job->resource->profile->pro_pic : null;
        $partner_order['resource_mobile'] = $job && $job->resource ? $job->resource->profile->mobile : null;
        $partner_order['category_app_banner'] = $job && $job->category ? $job->category->app_banner : null;
        $partner_order['category_banner'] = $job && $job->category ? $job->category->banner : null;
        $partner_order['rating'] = $job && $job->review ? (double)$job->review->rating : null;
        $partner_order['address'] = $partner_order->order->deliveryAddress->address;
        $partner_order['location'] = $partner_order->order->location ? $partner_order->order->location->name : $partner_order->order->deliveryAddress->address;
        $partner_order['total_price'] = (double)$partner_order->totalPrice;
        $partner_order['due_amount'] = (int)$partner_order->due;
        $partner_order['discount'] = (double)$partner_order->totalDiscount;
        $partner_order['sheba_collection'] = (int)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $job ? $job->status : null;
        $partner_order['isRentCar'] = $job ? $job->isRentCar() : null;
        $partner_order['is_on_premise'] = $job && $job->site == 'partner' ? 1 : 0;
        $partner_order['is_subscription_order'] = $partner_order->order->subscription_order_id ? 1 : 0;

        return $partner_order;
    }

    private function partnerOrdersSortBy($field, $orderBy, $all_partner_orders, $all_jobs)
    {
        if ($field == 'created_at') {
            $all_partner_orders = $all_partner_orders->$orderBy('created_at');
        } else {
            $all_jobs = $all_jobs->$orderBy($field);
            $final = collect();
            foreach ($all_jobs as $job) {
                $final->push($all_partner_orders->where('id', $job->partner_order_id)->first());
            }
            $all_partner_orders = $final->unique('id');
        }
        return $all_partner_orders;
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
