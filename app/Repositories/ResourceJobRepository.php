<?php namespace App\Repositories;

use App\Models\PartnerOrder;
use App\Sheba\UserRequestInformation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Sheba\Jobs\JobStatuses;

class ResourceJobRepository
{
    private $created_by_type = 'App\Models\Resource';

    /**
     * @param $jobs
     * @return array
     */
    public function rearrange($jobs)
    {
        $process_jobs = $jobs->whereIn('status', [JobStatuses::PROCESS, JobStatuses::SERVE_DUE]);
        $process_jobs = $process_jobs->map(function ($item) {
            if (in_array($item->preferred_time, constants('JOB_PREFERRED_TIMES'))) {
                return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
            } else {
                return array_add($item, 'preferred_time_priority', 500);
            }
        });
        $process_jobs = $process_jobs->sortBy(function ($job) {
            return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
        })->values()->all();
        $served_jobs = $jobs->where('status', JobStatuses::SERVED)->values()->all();
        $other_jobs = $jobs->filter(function ($job) {
            return !in_array($job->status, [JobStatuses::PROCESS, JobStatuses::SERVED, JobStatuses::SERVE_DUE]);
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
        $jobs = array_merge($served_jobs, array_merge($process_jobs, $other_jobs));
        return $jobs;
    }

    public function getJobs($resource)
    {
        $resource->load(['jobs' => function ($q) {
            $q->info()->whereHas('partnerOrder', function ($q) {
                $q->ongoing()->notBadDebt();
            })->tillNow()->with(['category', 'carRentalJobDetail', 'partner_order' => function ($q) {
                $q->with(['order', 'jobs' => function ($q) {
                    $q->with('service', 'jobServices', 'usedMaterials');
                }]);
            }, 'service', 'jobServices' => function ($q) {
                $q->with('service');
            }, 'usedMaterials']);
        }]);
        return $resource->jobs;
    }

    private function _getLastServedJobOfPartnerOrder($jobs)
    {
        $final_last_jobs = [];
        foreach ($jobs as $job) {
            $partner_order = $job->partner_order;
            $all_jobs_of_this_partner_order = $job->partner_order->jobs;
            $cancel_status = constants('JOB_STATUSES_SHOW')['Cancelled']['sheba'];
            $partner_order_other_jobs = $all_jobs_of_this_partner_order->reject(function ($item, $key) use ($job, $cancel_status) {
                return $item->id == $job->id || $item->status == $cancel_status;
            });
            if ($partner_order->closed_and_paid_at == null) {
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
            if (!$job->partner_order->order->deliveryAddress) {
                $job->partner_order->order->deliveryAddress = $job->partner_order->order->getTempAddress();
            }
            $job['delivery_name'] = $job->partner_order->order->deliveryAddress->name;
            $job['delivery_mobile'] = $job->partner_order->order->deliveryAddress->mobile;
            $job['delivery_address'] = $job->partner_order->order->deliveryAddress->address;
            $job['schedule_date_timestamp'] = (Carbon::parse($job->schedule_date))->timestamp;
            $job['schedule_timestamp'] = $job->partner_order->getVersion() == 'v2' ? Carbon::parse($job->schedule_date . ' ' . explode('-', $job->preferred_time)[0])->timestamp : Carbon::parse($job->schedule_date)->timestamp;
            $job['service_unit_price'] = (double)$job->service_unit_price;
            $job['category_name'] = $job->category ? $job->category->name : null;
            $job['preferred_time'] = $job->readable_preferred_time;
            $job['service_unit'] = null;
            if (count($job->jobServices) == 0) {
                $services = collect();
                $variables = json_decode($job->service_variables);
                $services->push(array('name' => $job->service_name, 'variables' => $variables, 'unit' => $job->service ? $job->service->unit : null, 'quantity' => $job->service_quantity));
            } else {
                $services = collect();
                foreach ($job->jobServices as $jobService) {
                    $variables = json_decode($jobService->variables);
                    $services->push(array('name' => $jobService->formatServiceName($job), 'variables' => $variables, 'unit' => $jobService->service ? $jobService->service->unit : null, 'quantity' => $jobService->quantity));
                }
            }
            $job['services'] = $services;
            $job['schedule_date'] = Carbon::parse($job->schedule_date)->format('Y-m-d');
            $job['code'] = $job->fullCode();
            $job->calculate(true);
            $job['total_price'] = (double)$job->grossPrice;
            $job['service_price'] = (double)$job->servicePrice;
            $job['material_price'] = (double)$job->materialPrice;
            $job['discount'] = (double)$job->discount;
            $job['version'] = $job->getVersion();
            $job['service_unit_price'] = (double)$job->service_unit_price;
            $job['isDue'] = $job->partner_order->closed_and_paid_at ? 0 : 1;
            $job['missed_at'] = $job->status == 'Schedule Due' ? $job->schedule_date : null;
            $job['pick_up_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null;
            $job['destination_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null;
            $job['drop_off_date'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y') : null;
            $job['drop_off_time'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A') : null;
            $job['estimated_distance'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null;
            $job['estimated_time'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null;
            $job['isRentCar'] = $job->isRentCar();
            $job['sheba_contribution'] = (double)$job->sheba_contribution;
            $job['partner_contribution'] = (double)$job->partner_contribution;
            removeRelationsAndFields($job);
        }
        return $jobs;
    }

    public function changeStatus($job, $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $job . '/change-status',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'status' => $request->status,
                        'created_by_type' => $this->created_by_type,
                        'partner_id' => $request->partner->id
                    ])
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function createNewJobWithExistingPartnerOrder($currentJobId, $existingPartnerOrderId, $resourceId, $request)
    {
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . "/api/new-job/existing-partner-order", [
            'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                'current_job_id' => $currentJobId,
                'existing_partner_order_id' => $existingPartnerOrderId,
                'resource_id' => $resourceId,
                'created_by_type' => 'App\Models\Partner'
            ])
        ]);
        return json_decode($res->getBody());
    }

    public function reschedule($job, $request)
    {
        try {
            $client = new Client();
            $data = [
                'resource_id' => $request->resource->id,
                'remember_token' => $request->resource->remember_token,
                'schedule_date' => $request->schedule_date,
                'preferred_time' => $request->preferred_time,
                'created_by_type' => $this->created_by_type,
            ];
            if ($request->has('schedule_change_reason')) $data['order_reschedule_reason'] = $request->schedule_change_reason;
            $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $job . '/reschedule',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), $data)
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function collectMoney(PartnerOrder $order, Request $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $order->id . '/collect',
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
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function calculateActionsForThisJob($job)
    {
        $partner_order = $job->partner_order;
        if (($job->status == 'Process' || $job->status == 'Serve Due') && $partner_order->due > 0) {
            $job['can_collect'] = $partner_order->payment_method != 'bad-debt';
        } elseif (($job->status == 'Process' || $job->status == 'Serve Due') && $partner_order->due == 0) {
            $job['can_serve'] = true;
        } elseif ($job->status == 'Served' && $partner_order->due > 0) {
            $job['can_collect'] = $partner_order->payment_method != 'bad-debt';
        } else {
            $job['can_process'] = true;
        }
        return $job;
    }
}
