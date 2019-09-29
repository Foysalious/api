<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class JobTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['services'];

    public function transform($job)
    {
        return [
            'id' => $job->id,
            'delivery_mobile' => $job->delivery_mobile,
            'delivery_address' => $job->delivery_address,
            'delivery_name' => $job->delivery_name,
            'schedule_date' => $job->schedule_date,
            'preferred_time' => $job->preferred_time,
            'order_code' => $job->order_code,
            'original_price' => $job->original_price,
            'discounted_price' => $job->price,
            'status' => constants('JOB_STATUSES_SHOW')[$job->status]['customer'],
            'discount' => $job->discount,
            'is_due' => $job->isDue,
            'resource' => array(
                'name' => $job->resource_name,
                'picture' => $job->resource_picture
            ),
            'partner' => array(
                'name' => $job->partner_name,
            )
        ];
    }

    public function includeServices($job)
    {
        $collection = $this->collection($job->services, new JobServiceTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}