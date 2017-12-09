<?php

namespace App\Repositories;


class PartnerJobRepository
{
    public function getJobInfo($job)
    {
        $job->calculate();
        $job['total_cost'] = $job->totalCost;
        $job['location'] = $job->partner_order->order->location->name;
        $job['service_unit_price'] = (double)$job->service_unit_price;
        $job['discount'] = (double)$job->discount;
        $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
        $job['resource_name'] = $job->resource != null ? $job->resource->profile->name : null;
        $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
        $job['materials'] = $job->usedMaterials;
        $job['total_materials'] = count($job->usedMaterials);
        return $job;
    }

}