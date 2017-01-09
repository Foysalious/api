<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    private $job_statuses_show;
    private $job_preferred_times;
    private $job_statuses;

    public function __construct()
    {
        $this->job_statuses_show = config('constants.JOB_STATUSES_SHOW');
        $this->job_statuses = constants('JOB_STATUSES');
    }

    public function getInfo($customer, $job)
    {
        $job = Job::find($job);
        if ($job->partner_order->order->customer_id == $customer) {
            $job = Job::with(['partner_order' => function ($query) {
                $query->select('id', 'partner_id', 'order_id')->with(['partner' => function ($query) {
                    $query->select('id', 'name');
                }])->with(['order' => function ($query) {
                    $query->select('id');
                }]);
            }])->with(['materials' => function ($query) {
                $query->select('material_name', 'material_price');
            }])->with(['service' => function ($query) {
                $query->select('id', 'name', 'variable_type', 'variables');
            }])->with(['review' => function ($query) {
                $query->select('job_id', 'review_title', 'review', 'rating');
            }])->where('id', $job->id)
                ->select('id', 'service_id', 'service_name', 'service_quantity', 'job_additional_info', 'service_option', 'discount', 'status', 'service_unit_price', 'created_at', 'partner_order_id')
                ->first();
            array_add($job, 'status_show', $this->job_statuses_show[array_search($job->status, $this->job_statuses)]);

            $job_model = Job::find($job->id);
            $job_model->calculate();
            array_add($job, 'material_cost', $job_model->materialCost);
            array_add($job, 'total_cost', $job_model->grossPrice);
            array_add($job, 'job_code', $job_model->code());
            array_add($job, 'service_price', $job_model->servicePrice);

            return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        }

    }

    public function getPreferredTimes()
    {
        return response()->json(['times' => config('constants.JOB_PREFERRED_TIMES'), 'code' => 200]);
    }
}
