<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use App\Models\PartnerOrder;
use Sheba\Services\FormatServices;

class BillInfo
{
    private $formatServices;

    public function __construct(FormatServices $formatServices)
    {
        $this->formatServices = $formatServices;
    }

    public function getBill(Job $job)
    {
        $job = $job->load(['partnerOrder.order', 'category', 'jobServices', 'usedMaterials' => function ($q) {
            $q->select('job_material.id', 'job_material.material_name', 'material_price', 'job_id');
        }]);
        $job->calculate(true);
        $service_list = [];
        if (count($job->jobServices) == 0) {
            $services = array();
            array_push($services, array('name' => $job->category ? $job->category->name : null,
                'price' => (double)$job->servicePrice,
                'unit' => $job->service->unit,
                'quantity' => $job->service_quantity));

            array_push($service_list, array(
                'name' => $job->service != null ? $job->service->name : null,
                'service_group' => [],
                'price' => (double)$job->servicePrice,
                'unit' => $job->service->unit,
                'quantity' => $job->service_quantity));
        } else {
            $services = array();
            $service_list = $this->formatServices->setJob($job)->formatServices();
            foreach ($job->jobServices as $jobService) {
                array_push($services, array(
                    'id' => $jobService->id,
                    'name' => $jobService->job->category ? $jobService->job->category->name : null,
                    'price' => (double)$jobService->unit_price * (double)$jobService->quantity,
                    'unit' => $jobService->service->unit, 'quantity' => $jobService->quantity
                ));
            }
        }
        /** @var PartnerOrder $partnerOrder */
        $partnerOrder = $job->partnerOrder;
        $partnerOrder->calculate(true);
        $bill = collect();
        $bill['total'] = (double)$partnerOrder->totalPrice;
        $bill['grand_total'] = (double)$partnerOrder->totalPrice;
        $bill['paid'] = (double)$partnerOrder->paid;
        $bill['due'] = (double)$partnerOrder->due;
        $bill['total_material_price'] = (double)$job->materialPrice;
        $bill['total_service_price'] = (double)$job->servicePrice;
        $bill['discount'] = (double)$job->discount;
        $bill['payment_method'] = $this->formatPaymentMethod($partnerOrder->payment_method);
        $bill['services'] = $services;
        $bill['service_list'] = $service_list;
        $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
        $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
        $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
        $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
        $bill['status'] = $job->status;
        $bill['materials'] = $job->usedMaterials;
        $bill['isPaid'] = $partnerOrder->isPaid() ? 1 : 0;
        $bill['isDue'] = $partnerOrder->isDue() ? 1 : 0;
        $bill['job_code'] = $job->fullcode();
        return $bill;
    }

    private function formatPaymentMethod($payment_method)
    {
        $payment_method = strtolower($payment_method);
        if ($payment_method=='cod') return 'Cash On Delivery';
        if ($payment_method=='wallet') return 'Sheba Credit';
        if ($payment_method=='cbl') return 'CBL';
        if ($payment_method=='bkash') return 'bKash';
        return ucwords(str_replace("-", " ", str_replace("_", " ", $payment_method)));
    }
}
