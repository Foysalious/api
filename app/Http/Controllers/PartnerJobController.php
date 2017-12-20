<?php

namespace App\Http\Controllers;

use App\Models\JobMaterial;
use App\Models\JobUpdateLog;
use App\Models\Resource;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
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
            list($offset, $limit) = calculatePagination($request);
            $partnerRepo = new PartnerRepository($request->partner);
            $statuses = $partnerRepo->resolveStatus($request->filter);
            $jobs = $partnerRepo->jobs($statuses);
            if (count($jobs) > 0) {
                $jobs = $jobs->sortByDesc('created_at');
                $jobs = $jobs->each(function ($job) {
                    $job['location'] = $job->partner_order->order->location->name;
                    $job['service_unit_price'] = (double)$job->service_unit_price;
                    $job['discount'] = (double)$job->discount;
                    $job['code'] = $job->partner_order->order->code();
                    $job['customer_name'] = $job->partner_order->order->customer->profile->name;
                    $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
                    $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
                    $job['rating'] = $job->review != null ? $job->review->rating : null;
                    removeRelationsFromModel($job);
                })->values()->all();
                $jobs = array_slice($jobs, $offset, $limit);
                return api_response($request, $jobs, 200, ['jobs' => $jobs]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
            return api_response($request, null, 500);
        }
    }

    public function update($partner, $job, Request $request)
    {
        try {
            $job = $request->job;
            $this->validate($request, [
                'schedule_date' => 'sometimes|required|date|after:yesterday',
                'preferred_time' => 'required_with:schedule_date|string',
                'resource_id' => 'required_without_all:schedule_date,preferred_time|not_in:' . $job->resource_id,
            ]);
            if ($request->has('schedule_date') && $request->has('preferred_time')) {
                $request->merge(['resource' => $request->manager_resource]);
                $response = $this->resourceJobRepository->reschedule($job->id, $request);
                return api_response($request, $response, $response->code);
            }
            if ($request->has('resource_id')) {
                if ($request->partner->hasThisResource((int)$request->resource_id, 'Handyman') && $job->hasStatus(['Accepted', 'Schedule_Due', 'Process'])) {
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
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
                    return api_response($request, null, 500);
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function jobUpdateLog($job_id, $log, $created_by)
    {
        $logData = [
            'job_id' => $job_id,
            'log' => $log,
            'created_by' => $created_by->id,
            'created_by_name' => class_basename($created_by) . "-" . $created_by->profile->name
        ];
        JobUpdateLog::create(($logData));
    }

    private function assignResource($job, $resource_id, Resource $manager_resource)
    {
        $updatedData = [
            'msg' => 'Resource Change',
            'old_resource_id' => $job->resource_id,
            'new_resource_id' => (int)$resource_id
        ];
        $job->resource_id = $resource_id;
        $job->update();
        $this->jobUpdateLog($job->id, json_encode($updatedData), $manager_resource);
        return $job;
    }


}