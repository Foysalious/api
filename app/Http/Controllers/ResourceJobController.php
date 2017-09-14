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
            $resource = $request->resource->load(['jobs' => function ($q) {
                $q->select('id', 'resource_id', 'schedule_date', 'preferred_time', 'service_name', 'status', 'partner_order_id')->where('schedule_date', '<=', date('Y-m-d'))->whereIn('status', ['Accepted', 'Process', 'Schedule Due'])->with(['partner_order' => function ($q) {
                    $q->with('order.customer.profile');
                }]);
            }]);
            $jobs = $resource->jobs;
            if (count($jobs) != 0) {
                foreach ($jobs as $job) {
                    $job['customer_name'] = $job->partner_order->order->customer->profile->name;
                    $job['customer_mobile'] = $job->partner_order->order->customer->profile->mobile;
                    $job['address'] = $job->partner_order->order->delivery_address;
                    $job['code'] = $job->code();
                    array_forget($job, 'partner_order');
                    array_forget($job, 'partner_order_id');
                    array_forget($job, 'resource_id');
                }
                $jobs = $this->resourceJobRepository->rearrange($jobs);
                list($offset, $limit) = calculatePagination($request);
                $jobs = array_slice($jobs, $offset, $limit);
                return api_response($request, $jobs, 200, ['jobs' => $jobs]);
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
                $response = $this->_changeStatus($job, $request);
                if ($response != null) {
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            } elseif ($request->has('schedule_date') && $request->has('preferred_time')) {
                $response = $this->_reschedule($job, $request);
                if ($response != null) {
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
            $response = $this->_collectMoney($partner_order, $request);
            if ($response != null) {
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
            $job = Job::where('id', $job)->select('id', 'resource_id')->first();
            if ($job->resource_id != $resource->id) {
                return api_response($request, null, 403);
            }
            $jobs = $this->api->get('resources/' . $resource->id . '/jobs?remember_token=' . $resource->remember_token . '&limit=1');
            if ($jobs != null) {
                if ($jobs[0]->status == 'Process') {
                    $job['can_process'] = false;
                }
                return api_response($request, $job, 200, ['job' => $job]);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    private function _changeStatus($job, $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $job . '/change-status',
                [
                    'form_params' => [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'status' => $request->status
                    ]
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }

    private function _reschedule($job, $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $job . '/reschedule',
                [
                    'form_params' => [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'schedule_date' => $request->schedule_date,
                        'preferred_time' => $request->preferred_time,
                    ]
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }

    private function _collectMoney(PartnerOrder $order, Request $request)
    {
        try {
            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/partner-order/' . $order->id . '/collect',
                [
                    'form_params' => [
                        'resource_id' => $request->resource->id,
                        'remember_token' => $request->resource->remember_token,
                        'partner_collection' => $request->amount,
                    ]
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }
}
