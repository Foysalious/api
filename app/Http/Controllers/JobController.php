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

    public function getInfo($job)
    {
        $job = Job::with(['partner_order' => function ($query)
        {
            $query->select('id', 'partner_id')->with(['partner' => function ($query)
            {
                $query->select('id', 'name');
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
        }])->where('id', $job)
            ->select('id', 'job_code', 'service_id', 'service_name', 'service_option', 'status', 'service_cost', 'material_cost', 'total_cost', 'created_at', 'partner_order_id')
            ->first();
        array_add($job, 'status_show', $this->job_statuses_show[$job->status]);
        return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
    }
}
