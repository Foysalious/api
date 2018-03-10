<?php

namespace App\Repositories;


use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\ResourceSchedule;
use App\Sheba\UserRequestInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResourceJobRepository
{
    private $created_by_type = 'App\Models\Resource';

    public function rearrange($jobs)
    {
        $process_job = $jobs->where('status', 'Process');
        $process_job = $process_job->map(function ($item) {
            if (in_array($item->preferred_time, constants('JOB_PREFERRED_TIMES'))) {
                return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
            } else {
                return array_add($item, 'preferred_time_priority', 500);
            }
        });
        $process_job = $process_job->sortBy(function ($job) {
            return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
        })->values()->all();

        $served_jobs = $this->_getLastServedJobOfPartnerOrder($jobs->where('status', 'Served')->values()->all());
        $served_jobs = collect($served_jobs)->filter(function ($job) {
            return $job->partner_order->payment_method != 'bad-debt';
        })->values()->all();
        $other_jobs = $jobs->filter(function ($job) {
            return $job->status != 'Process' && $job->status != 'Served';
        });
        $other_jobs = $other_jobs->map(function ($item) {
            if (in_array($item->preferred_time, constants('JOB_PREFERRED_TIMES'))) {
                return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
            } else {
                return array_add($item, 'preferred_time_priority', 500);
            }
        });
        $other_jobs = $other_jobs->sortBy(function ($job) {
            return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
        })->values()->all();
        $jobs = array_merge($served_jobs, array_merge($process_job, $other_jobs));
        return $jobs;
    }

    public function getJobs($resource)
    {
        $resource->load(['jobs' => function ($q) {
            $q->info()->validStatus()->tillNow()->with('category', 'partner_order.order', 'service');
        }]);
        return $resource->jobs;
    }

    private function _getLastServedJobOfPartnerOrder($jobs)
    {
        $final_last_jobs = [];
        foreach ($jobs as $job) {
            $partner_order = $job->partner_order;
            $partner_order->calculate(true);
            $all_jobs_of_this_partner_order = $job->partner_order->jobs;
            $cancel_status = constants('JOB_STATUSES_SHOW')['Cancelled']['sheba'];
            $partner_order_other_jobs = $all_jobs_of_this_partner_order->reject(function ($item, $key) use ($job, $cancel_status) {
                return $item->id == $job->id || $item->status == $cancel_status;
            });
            if ((double)$partner_order->due > 0) {
                if ($partner_order_other_jobs->count() == 0) {
                    array_push($final_last_jobs, $job);
                } //all other jobs are served. Then check if job is the last job of partner order
                else if ($partner_order_other_jobs->where('status', 'Served')->count() == $partner_order_other_jobs->count()) {
                    $last_job = $all_jobs_of_this_partner_order->where('status', 'Served')->last();
                    if ($last_job->id == $job->id) {
                        array_push($final_last_jobs, $job);
                    }
                }
            }

        }
        return $final_last_jobs;
    }

    public function addJobInformationForAPI($jobs)
    {
        foreach ($jobs as $job) {
            $job['delivery_name'] = $job->partner_order->order->delivery_name;
            $job['delivery_mobile'] = $job->partner_order->order->delivery_mobile;
            $job['delivery_address'] = $job->partner_order->order->delivery_address;
            $job['schedule_date_timestamp'] = (Carbon::parse($job->schedule_date))->timestamp;
            $job['service_unit_price'] = (double)$job->service_unit_price;
            $job['category_name'] = $job->category ? $job->category->nam : null;
            $job['service_unit'] = null;
            if (count($job->jobServices) == 0) {
                $services = collect();
                $variables = json_decode($job->service_variables);
                $services->push(array('name' => $job->service_name, 'variables' => $variables, 'unit' => $job->service->unit, 'quantity' => $job->service_quantity));
            } else {
                $services = collect();
                foreach ($job->jobServices as $jobService) {
                    $variables = json_decode($jobService->variables);
                    $services->push(array('name' => $jobService->service->name, 'variables' => $variables, 'unit' => $jobService->unit, 'quantity' => $jobService->quantity));
                }
            }
            $job['services'] = $services;
            $job['schedule_date'] = Carbon::parse($job->schedule_date)->format('jS M, Y');
            $job['code'] = $job->fullCode();
            $job->calculate(true);
            $job['total_price'] = (double)$job->grossPrice;
            $job['service_price'] = (double)$job->servicePrice;
            $job['material_price'] = (double)$job->materialPrice;
            $job['discount'] = (double)$job->discount;
            $job['service_unit_price'] = (double)$job->service_unit_price;
            $job['isDue'] = $job->partner_order->closed_and_paid_at ? 1 : 0;
            $job['missed_at'] = $job->status == 'Schedule_date' ? $job->schedule_date : null;
            $this->_stripUnwantedInformationForAPI($job);
        }
        return $jobs;
    }

    private function _stripUnwantedInformationForAPI($job)
    {
        array_forget($job, 'partner_order');
        array_forget($job, 'partner_order_id');
        array_forget($job, 'resource_id');
        array_forget($job, 'service_id');
        array_forget($job, 'service');
        array_forget($job, 'usedMaterials');
        array_forget($job, 'jobServices');
        array_forget($job, 'category');
        return $job;
    }

    public function changeStatus($job, $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $job . '/change-status',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'status' => $request->status,
                        'created_by_type' => $this->created_by_type
                    ])
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

    public function reschedule($job, $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $job . '/reschedule',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'schedule_date' => $request->schedule_date,
                        'preferred_time' => $request->preferred_time,
                        'created_by_type' => $this->created_by_type,
                    ])
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

    public function collectMoney(PartnerOrder $order, Request $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/partner-order/' . $order->id . '/collect',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'partner_collection' => $request->amount,
                        'created_by_type' => $this->created_by_type
                    ])
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

    public function calculateActionsForThisJob($first_job_from_list, $job)
    {
        if ($job->status == 'Served') {
            if ($first_job_from_list->status == 'Served' && $job->id == $first_job_from_list->id) {
                $partner_order = $job->partner_order;
                if ($partner_order->payment_method == 'bad-debt') {
                    $job['can_collect'] = false;
                } else {
                    $job['can_collect'] = true;
                }
                $partner_order->calculate(true);
                $job['collect_money'] = (double)$partner_order->due;
                array_forget($job, 'partner_order');
            }
        } elseif ($job->status == 'Process') {
            if ($first_job_from_list->status == 'Process' && $job->id == $first_job_from_list->id) {
                $job['can_serve'] = true;
            }
        } else {
            if ($first_job_from_list->status != 'Process' && $first_job_from_list->status != 'Served') {
                $job['can_process'] = true;
            }
        }
        return $job;
    }

    public function book(Job $job, $created_by)
    {
        $resource_schedule = new ResourceSchedule();
        $resource_schedule->job_id = $job->id;
        $resource_schedule->resource_id = $job->resource_id;
        $resource_schedule->start = $job->preferred_time_start;
        $resource_schedule->end = explode('-', $job->preferred_time)[1];
        $resource_schedule->created_by = $created_by->id;
        $resource_schedule->created_by_name = class_basename($created_by) . "-" . $created_by->profile->name;
        $resource_schedule->save();
    }
}