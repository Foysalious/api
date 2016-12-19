<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller {
    private $job_statuses_show;

    public function __construct()
    {
        $this->job_statuses_show = config('constants.JOB_STATUSES_SHOW');
    }

    public function getInfo($customer, $job)
    {
        $job = Job::find($job);
        if ($job->partner_order->order->customer_id == $customer)
        {
            $job = Job::with(['partner_order' => function ($query)
            {
                $query->select('id', 'partner_id', 'order_id')->with(['partner' => function ($query)
                {
                    $query->select('id', 'name');
                }])->with(['order' => function ($query)
                {
                    $query->select('id', 'order_code');
                }]);
            }])->with(['materials' => function ($query)
            {
                $query->select('material_name', 'material_price');
            }])->with(['service' => function ($query)
            {
                $query->select('id', 'name', 'variable_type', 'variables');
            }])->with(['review' => function ($query)
            {
                $query->select('job_id', 'review_title', 'review', 'rating');
            }])->where('id', $job->id)
                ->select('id', 'job_code', 'service_id', 'service_name', 'service_option', 'discount', 'status', 'service_price', 'created_at', 'partner_order_id')
                ->first();
            array_add($job, 'status_show', $this->job_statuses_show[$job->status]);

            $job_model = Job::find($job->id);
            $job_model->calculate();
            array_add($job, 'material_cost', $job_model->materialCost);
            array_add($job, 'total_cost', $job_model->grossPrice);

            return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
        }
        else
        {
            return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        }

    }
}
