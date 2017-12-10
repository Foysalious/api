<?php

namespace App\Repositories;


class PartnerOrderRepository
{
    private $partnerJobRepository;

    public function __construct()
    {
        $this->partnerJobRepository = new PartnerJobRepository();
    }

    public function getOrderDetails($request)
    {
        $partner_order = $this->getOrderInfo($this->loadAllRelatedRelations($request->partner_order));
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

    private function loadAllRelatedRelations($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->orderBy('schedule_date')->with(['usedMaterials' => function ($q) {
                $q->select('id', 'job_id', 'material_name', 'material_price');
            }, 'resource.profile']);
        }]);
    }

    private function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        } else {
            return constants('JOB_STATUSES');
        }
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']);
        }
    }

    public function getOrderInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['address'] = $partner_order->order->delivery_address;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['due_amount'] = (double)$partner_order->due;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
        return $partner_order;
    }
}