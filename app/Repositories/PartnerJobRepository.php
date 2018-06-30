<?php

namespace App\Repositories;


class PartnerJobRepository
{
    public function getJobInfo($job)
    {
        $job->calculate(true);
        $job['total_cost'] = $job->totalCost;
        $job['location'] = $job->partner_order->order->location->name;
        $job['discount'] = (double)$job->discount;
        $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
        $job['resource_name'] = $job->resource != null ? $job->resource->profile->name : null;
        $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
        $job['materials'] = count($job->usedMaterials) > 0 ? $job->usedMaterials->each(function ($item, $key) {
            removeRelationsAndFields($item);
        })->values()->all() : null;
        $job['total_materials'] = count($job->usedMaterials);
        return $job;
    }

    public function getJobServiceInfo($job_service)
    {
        return (collect($job_service)->only('id', 'job_id', 'service_id', 'name', 'variable_type', 'variables', 'option', 'additional_info', 'quantity', 'unit_price'))->toArray();
    }


}