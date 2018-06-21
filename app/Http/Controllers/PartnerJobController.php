<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobMaterial;
use App\Models\JobUpdateLog;
use App\Models\Resource;
use App\Repositories\NotificationRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PushNotificationRepository;
use App\Repositories\ResourceJobRepository;
use App\Sheba\JobTime;
use App\Sheba\UserRequestInformation;
use Carbon\Carbon;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class PartnerJobController extends Controller
{
    private $resourceJobRepository;

    public function __construct()
    {
        $this->resourceJobRepository = new ResourceJobRepository();
    }

    public function index($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:new,ongoing,history'
            ]);
            $filter = $request->filter;
            $partner = $request->partner->load(['partnerOrders' => function ($q) use ($filter, $request) {
                $q->$filter()->with(['order' => function ($q) {
                    $q->with('location', 'customer.profile');
                }])->with(['jobs' => function ($q) use ($filter, $request) {
                    $q->info()->whereIn('status', (new PartnerOrderRepository())->getStatusFromRequest($request))->orderBy('id', 'desc')->with(['category', 'usedMaterials' => function ($q) {
                        $q->select('id', 'job_id', 'material_name', 'material_price');
                    }, 'resource.profile', 'review']);
                }]);
            }]);
            $jobs = collect();
            foreach ($partner->partnerOrders as $partnerOrder) {
                foreach ($partnerOrder->jobs as $job) {
                    $job['location'] = $partnerOrder->order->location->name;
                    $job['service_unit_price'] = (double)$job->service_unit_price;
                    $job['discount'] = (double)$job->discount;
                    $job['code'] = $partnerOrder->order->code();
                    $job['category_name'] = $job->category ? $job->category->name : null;
                    $job['customer_name'] = $partnerOrder->order->customer ? $partnerOrder->order->customer->profile->name : null;
                    $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
                    $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
                    $job['resource_name'] = $job->resource != null ? $job->resource->profile->name : '';
                    $job['schedule_timestamp'] = Carbon::parse($job->schedule_date . ' ' . explode('-', $job->preferred_time)[0])->timestamp;
                    $job['preferred_time'] = humanReadableShebaTime($job->readable_preferred_time);
                    $job['rating'] = $job->review != null ? $job->review->rating : null;
                    $job['version'] = $partnerOrder->order->getVersion();
                    if ($partnerOrder->closed_and_paid_at != null) {
                        $job['completed_at_timestamp'] = $partnerOrder->closed_and_paid_at->timestamp;
                        $job['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at->format('jS F');
                    } else {
                        $job['completed_at_timestamp'] = null;
                        $job['closed_and_paid_at'] = null;
                    }
                    removeRelationsFromModel($job);
                    $jobs->push($job);
                }
            }
            if (count($jobs) > 0) {
                if ($filter == 'ongoing') {
                    $group_by_jobs = $jobs->groupBy('schedule_date')->sortBy(function ($item, $key) {
                        return $key;
                    });
                    $final = collect();
                    foreach ($group_by_jobs as $key => $jobs) {
                        $jobs = $jobs->sortBy('schedule_timestamp');
                        foreach ($jobs as $job) {
                            $final->push($job);
                        }
                    }
                    $jobs = $final;
                } else {
                    $jobs = $jobs->sortByDesc('id');
                }
                list($offset, $limit) = calculatePagination($request);
                $jobs = $jobs->splice($offset, $limit);
                $resources = collect();
                foreach ($jobs->groupBy('resource_id') as $key => $resource) {
                    if (!empty($key)) {
                        $resources->push(array(
                            'id' => (int)$key,
                            'name' => $resource->first()->resource_name
                        ));
                    }
                }
                return api_response($request, $jobs, 200, ['jobs' => $jobs, 'resources' => $resources]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function acceptJobAndAssignResource($partner, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'resource_id' => 'required|int'
            ]);
            $job = $request->job;
            if ($request->partner->hasThisResource((int)$request->resource_id, 'Handyman') && $job->hasStatus(['Pending', 'Not_Responded'])) {
                $request->merge(['remember_token' => $request->manager_resource->remember_token, 'status' => 'Accepted', 'resource' => $request->manager_resource]);
                $response = $this->resourceJobRepository->changeStatus($job->id, $request);
                if ($response) {
                    if ($response->code == 200) {
                        $job = $this->assignResource($job, $request->resource_id, $request->manager_resource);
                        if ($job->crm_id != null) {
                            $order = $job->partnerOrder->order;
                            (new NotificationRepository())->sendToCRM($job->crm_id, "Partner has accepted this job, ID-" . $order->code(), $order);
                        }
                        return api_response($request, $job, 200);
                    }
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            }
            return api_response($request, null, 403);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function declineJob($partner, $job, Request $request)
    {
        try {
            $request->merge(['remember_token' => $request->manager_resource->remember_token, 'status' => 'Declined', 'resource' => $request->manager_resource]);
            $response = $this->resourceJobRepository->changeStatus($request->job->id, $request);
            if ($response) {
                return api_response($request, $response, $response->code);
            }
            return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($partner, $job, Request $request)
    {
        try {
            $job = $request->job;
            $this->validate($request, [
                'schedule_date' => 'sometimes|required|date|after:' . Carbon::yesterday(),
                'preferred_time' => 'required_with:schedule_date|string',
                'resource_id' => 'required_without_all:schedule_date,preferred_time|not_in:' . $job->resource_id,
            ]);
            if ($request->has('schedule_date') && $request->has('preferred_time')) {
                $job_time = new JobTime($request->day, $request->time);
                $job_time->validate();
                if (!$job_time->isValid) {
                    return api_response($request, null, 400, ['message' => $job_time->error_message]);
                }
                if (!scheduler(Resource::find((int)$job->resource_id))->isAvailableForCategory($request->schedule_date, explode('-', $request->preferred_time)[0], $job->category)) {
                    return api_response($request, null, 403, ['message' => 'Resource is not available at this time. Please select different date time or change the resource']);
                }
                $request->merge(['resource' => $request->manager_resource]);
                $response = $this->resourceJobRepository->reschedule($job->id, $request);
                if ($response) {
                    return api_response($request, $response, $response->code);
                } else {
                    return api_response($request, null, 500);
                }
            }
            if ($request->has('resource_id')) {
                if (!scheduler(Resource::find((int)$request->resource_id))->isAvailableForCategory($job->schedule_date, explode('-', $job->preferred_time)[0], $job->category)) {
                    return api_response($request, null, 403, ['message' => 'Resource is not available at this time. Please select different date time or change the resource']);
                }
                if ($request->partner->hasThisResource((int)$request->resource_id, 'Handyman') && $job->hasStatus(['Accepted', 'Schedule_Due', 'Process', 'Serve_Due'])) {
                    $job = $this->assignResource($job, $request->resource_id, $request->manager_resource);
                    return api_response($request, $job, 200);
                }
                return api_response($request, null, 403);
            }
            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getMaterials($partner, $job, Request $request)
    {
        try {
            $materials = $request->job->usedMaterials;
            if (count($materials) > 0) {
                $materials->each(function ($item, $key) {
                    $item['material_price'] = (double)$item->material_price;
                    $item['added_by'] = $item->created_by_name;
                    removeSelectedFieldsFromModel($item);
                });
                return api_response($request, $materials, 200, ['materials' => $materials, 'total_material_price' => $materials->sum('material_price')]);
            }
            return api_response($request, $job, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addMaterial($partner, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'price' => 'required|numeric|min:1'
            ]);
            try {
                $material = new JobMaterial();
                DB::transaction(function () use ($job, $request, $material) {
                    $job = $request->job;
                    $material->material_name = $request->name;
                    $material->material_price = (double)$request->price;
                    $material->job_id = $job->id;
                    $material->created_by = $request->manager_resource->id;
                    $material->created_by_name = 'Resource-' . $request->manager_resource->profile->name;
                    $material->save();
                });
                return api_response($request, $material, 200);
            } catch (QueryException $e) {
                app('sentry')->captureException($e);
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateMaterial($partner, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'material_id' => 'required|numeric',
                'name' => 'required|string',
                'price' => 'required|numeric|min:1'
            ]);
            $job = $request->job;
            $material = $job->usedMaterials->where('id', (int)$request->material_id)->first();
            if ($material) {
                try {
                    DB::transaction(function () use ($job, $request, $material) {
                        $material->material_name = $request->name;
                        $material->material_price = $request->price;
                        $material->updated_by = $request->manager_resource->id;
                        $material->updated_by_name = 'Resource-' . $request->manager_resource->profile->name;
                        $material->update();
                        return api_response($request, null, 200);
                    });
                    return api_response($request, $material, 200);
                } catch (QueryException $e) {
                    app('sentry')->captureException($e);
                    return api_response($request, null, 500);
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function jobUpdateLog($job_id, $log, $created_by)
    {
        $logData = [
            'job_id' => $job_id,
            'log' => $log,
            'created_by' => $created_by->id,
            'created_by_name' => class_basename($created_by) . "-" . $created_by->profile->name,
            'created_by_type' => 'App/Models/' . class_basename($created_by)
        ];
        JobUpdateLog::create(array_merge((new UserRequestInformation(\request()))->getInformationArray(), $logData));
    }

    private function assignResource(Job $job, $resource_id, Resource $manager_resource)
    {
        $old_resource = $job->resource_id;
        $new_resource = ( int)$resource_id;
        $updatedData = [
            'msg' => 'Resource Change',
            'old_resource_id' => $old_resource,
            'new_resource_id' => $new_resource
        ];
        $job->resource_id = $resource_id;
        $job->update();
        if (empty($old_resource)) {
            scheduler($job->resource)->book($job);
        } else {
            scheduler($job->resource)->reAssign($job);
        }
        $this->jobUpdateLog($job->id, json_encode($updatedData), $manager_resource);

        (new PushNotificationRepository())->send([
            "title" => 'Resource has been assigned',
            "message" => $job->resource->profile->name . " has been added as a resource for your job.",
            "event_type" => 'Job',
            "event_id" => $job->id
        ], 'customer_' . $job->partner_order->order->customer->id);

        (new PushNotificationRepository())->send([
            "title" => 'Assigned to a new job',
            "message" => 'You have been assigned to a new job. Job ID: ' . $job->fullCode(),
            "event_type" => 'Job',
            "event_id" => $job->id
        ], 'resource_' . $job->resource_id);
        return $job;
    }


}