<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobCancelLog;
use Illuminate\Http\Request;

class JobController extends Controller
{
    private $job_statuses_show;
    private $job_preferred_times;
    private $job_statuses;

    public function __construct()
    {
        $this->job_statuses_show = config('constants.JOB_STATUSES_SHOW');
        $this->job_statuses = config('constants.JOB_STATUSES');
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
            }])->with(['usedMaterials' => function ($query) {
                $query->select('id', 'job_id', 'material_name', 'material_price');
            }])->with(['service' => function ($query) {
                $query->select('id', 'name');
            }])->with(['review' => function ($query) {
                $query->select('job_id', 'review_title', 'review', 'rating');
            }])->where('id', $job->id)
                ->select('id', 'service_id', 'service_name', 'service_quantity', 'service_variable_type', 'service_variables', 'job_additional_info', 'service_option', 'discount', 'status', 'service_unit_price', 'created_at', 'partner_order_id')
                ->first();
            array_add($job, 'status_show', $this->job_statuses_show[array_search($job->status, $this->job_statuses)]);

            $job_model = Job::find($job->id);
            $job_model->calculate();
            array_add($job, 'material_price', $job_model->materialPrice);
            array_add($job, 'total_cost', $job_model->grossPrice);
            array_add($job, 'job_code', $job_model->code());
            array_add($job, 'time', $job->created_at->format('jS M, Y'));
            array_forget($job, 'created_at');
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

    public function cancelJobReasons()
    {
        return response()->json(['reasons' => config('constants.JOB_CANCEL_REASONS_FROM_CUSTOMER'), 'code' => 200]);
    }

    public function cancelJob($customer, $job, Request $request)
    {
        $job = Job::find($job);
        $previous_status = $job->status;
        if ($previous_status == config("constants.JOB_STATUSES_SHOW")['Pending']['customer']) {
            $job->status = 'Cancelled';
            if ($job->update()) {
                $job_cancel = new JobCancelLog();
                $job_cancel->job_id = $job->id;
                $job_cancel->from_status = $previous_status;
                $job_cancel->cancel_reason = 'Customer Dependency';
                $job_cancel->log = 'Job has been cancelled by customer from front-end';
                $job_cancel->cancel_reason_details = $request->reason;
                $job_cancel->created_by_name = 'Customer';
                if ($job_cancel->save()) {
                    return response()->json(['msg' => 'Job Cancelled Successfully!', 'code' => 200]);
                }
            }
        } else {
            return response()->json(['code' => 404]);
        }
    }
}
