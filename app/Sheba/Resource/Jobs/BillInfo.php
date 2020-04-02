<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;

class BillInfo
{
    public function getBill(Job $job)
    {
        $job = $job->load(['partnerOrder.order', 'category', 'jobServices', 'usedMaterials' => function ($q) {
            $q->select('job_material.id', 'job_material.material_name', 'material_price', 'job_id');
        }]);
        $job->calculate(true);
        if (count($job->jobServices) == 0) {
            $services = array();
            array_push($services, array('name' => $job->category ? $job->category->name : null,
                'price' => (double)$job->servicePrice,
                'unit' => $job->service->unit,
                'quantity' => $job->service_quantity));
        } else {
            $services = array();
            foreach ($job->jobServices as $jobService) {
                array_push($services, array(
                    'name' => $jobService->job->category ? $jobService->job->category->name : null,
                    'price' => (double)$jobService->unit_price * (double)$jobService->quantity,
                    'unit' => $jobService->service->unit, 'quantity' => $jobService->quantity
                ));
            }
        }
        $partnerOrder = $job->partnerOrder;
        $partnerOrder->calculate(true);
        $bill = collect();
        $bill['total'] = (double)$partnerOrder->totalPrice;
        $bill['paid'] = (double)$partnerOrder->paid;
        $bill['due'] = (double)$partnerOrder->due;
        $bill['total_material_price'] = (double)$job->materialPrice;
        $bill['total_service_price'] = (double)$job->servicePrice;
        $bill['discount'] = (double)$job->discount;
        $bill['services'] = $services;
        $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
        $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
        $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
        $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
        $bill['status'] = $job->status;
        $bill['materials'] = $job->usedMaterials;
        $bill['isPaid'] = $job->partnerOrder->closed_at_paid ? 1 : 0;
        $bill['isDue'] = $job->partnerOrder->closed_at_paid == null ? 1 : 0;
        $bill['job_code'] = $job->fullcode();
        return $bill;
    }
}