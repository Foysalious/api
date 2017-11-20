<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobCancelLog;
use App\Repositories\PapRepository;
use Illuminate\Http\Request;
use DB;

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

    public function getInfo($customer, $job, Request $request)
    {
        $job = Job::find($job);
        if ($job != null) {
            if ($job->partner_order->order->customer_id == $customer) {
                $job = Job::with(['partner_order' => function ($query) {
                    $query->select('id', 'partner_id', 'order_id')->with(['partner' => function ($query) {
                        $query->select('id', 'name');
                    }])->with(['order' => function ($query) {
                        $query->select('id');
                    }]);
                }])->with(['resource' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name', 'mobile', 'pro_pic');
                    }]);
                }])->with(['usedMaterials' => function ($query) {
                    $query->select('id', 'job_id', 'material_name', 'material_price');
                }])->with(['service' => function ($query) {
                    $query->select('id', 'name','unit');
                }])->with(['review' => function ($query) {
                    $query->select('job_id', 'review_title', 'review', 'rating');
                }])->where('id', $job->id)
                    ->select('id', 'service_id', 'resource_id', DB::raw('DATE_FORMAT(schedule_date, "%M %d, %Y") as schedule_date'), DB::raw('DATE_FORMAT(delivered_date, "%M %d, %Y at %h:%i %p") as delivered_date'), 'created_at', 'preferred_time', 'service_name', 'service_quantity', 'service_variable_type', 'service_variables', 'job_additional_info', 'service_option', 'discount', 'status', 'service_unit_price', 'partner_order_id')
                    ->first();
                array_add($job, 'status_show', $this->job_statuses_show[array_search($job->status, $this->job_statuses)]);

                $job_model = Job::find($job->id);
                $job_model->calculate();
                array_add($job, 'material_price', $job_model->materialPrice);
                array_add($job, 'total_cost', $job_model->grossPrice);
                array_add($job, 'job_code', $job_model->fullCode());
                array_add($job, 'time', $job->created_at->format('jS M, Y'));
                array_forget($job, 'created_at');
                array_add($job, 'service_price', $job_model->servicePrice);
                if ($job->resource != null) {
                    $profile = $job->resource->profile;
                    array_forget($job, 'resource');
                    $job['resource'] = $profile;
                } else {
                    $job['resource'] = null;
                }

                return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
            } else {
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
            }
        } else {
            return api_response($request, null, 404);
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
//                    $order = $job->partner_order->order;
//                    $order->calculate();
//                    if ($order->status == constants('ORDER_STATUSES_SHOW')['Cancelled']['sheba']) {
//                        (new PapRepository())->refund($order->code());
//                    }
                    return response()->json(['msg' => 'Job Cancelled Successfully!', 'code' => 200]);
                }
            }
        } else {
            return response()->json(['code' => 404]);
        }
    }
}
