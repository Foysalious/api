<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Repositories\ResourceJobRepository;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use DB;

class ResourceJobController extends Controller
{
    use Helpers;
    private $resourceJobRepository;

    public function __construct()
    {
        $this->resourceJobRepository = new ResourceJobRepository();
    }

    public function index($resource, Request $request)
    {
        try {
            $jobs = $this->resourceJobRepository->getJobs($request->resource);
            $jobs = $this->resourceJobRepository->rearrange($jobs);
            list($offset, $limit) = calculatePagination($request);
            $jobs = array_slice($jobs, $offset, $limit);
            if (count($jobs) != 0) {
                return api_response($request, $jobs, 200, ['jobs' => $this->resourceJobRepository->addJobInformationForAPI($jobs)]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function update($resource, $job, Request $request)
    {
        try {
            if ($request->has('status')) {
                $response = $this->resourceJobRepository->changeStatus($job, $request);
                if ($response) {
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            } elseif ($request->has('schedule_date') && $request->has('preferred_time')) {
                $response = $this->resourceJobRepository->reschedule($job, $request);
                if ($response) {
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            }
            return api_response($request, null, 400);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function collect($resource, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return api_response($request, null, 500, ['message' => $validator->errors()->all()[0]]);
            }
            $partner_order = (Job::find($job))->partner_order;
            $response = $this->resourceJobRepository->collectMoney($partner_order, $request);
            if ($response) {
                return api_response($request, $response, $response->code);
            }
            return api_response($request, null, 500);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($resource, $job, Request $request)
    {
        try {
            $resource = $request->resource;
            $job = Job::select('id', 'status', 'resource_id', 'partner_order_id')->where('id', $job)->first();
            if (!$job) {
                return api_response($request, null, 404);
            }
            if ($job->resource_id != $resource->id) {
                return api_response($request, null, 403);
            }
            $job['can_process'] = false;
            $job['can_serve'] = false;
            $job['can_collect'] = false;
            $jobs = $this->api->get('resources/' . $resource->id . '/jobs?remember_token=' . $resource->remember_token . '&limit=1');
            if ($jobs) {
                $job = $this->resourceJobRepository->calculateActionsForThisJob($jobs[0], $job);
            }
            return api_response($request, $job, 200, ['job' => $job]);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

}
