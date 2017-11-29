<?php

namespace App\Http\Controllers;

use App\Models\JobMaterial;
use App\Models\Material;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
use Illuminate\Http\Request;
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
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:new,ongoing,history'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partner = $request->partner;
            $partnerRepo = new PartnerRepository($partner);
            $statuses = $partnerRepo->resolveStatus($request->status);
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
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }


    public function acceptJobAndAssignResource($partner, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'resource_id' => 'required|int'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $job = $request->job;
            $to_be_assigned_resource = $request->partner->resources->where('id', (int)$request->resource_id)->where('pivot.resource_type', 'Handyman')->first();
            if ($to_be_assigned_resource != null) {
                $request->merge(['remember_token' => $to_be_assigned_resource->remember_token, 'status' => 'Accepted', 'resource' => $request->manager_resource]);
                $response = $this->resourceJobRepository->changeStatus($job->id, $request);
                if ($response) {
                    if ($response->code == 200) {
                        $job->resource_id = $request->resource_id;
                        $job->update();
                        return api_response($request, $job, 200);
                    } else {
                        return api_response($request, $response, $response->code);
                    }
                }
                return api_response($request, null, 500);
            } else {
                return api_response($request, null, 403);
            }
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
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function update($partner, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_date' => 'sometimes|required|string',
                'preferred_time' => 'required_with:schedule_date|string',
                'resource_id' => 'required_without_all:schedule_date,preferred_time',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $job = $request->job;
            if ($request->has('schedule_date')) {
                $request->merge(['resource' => $request->manager_resource]);
                $response = $this->resourceJobRepository->reschedule($request->job->id, $request);
                if (!$response) {
                    return api_response($request, null, 500);
                }
                if ($response->code != 200) {
                    return api_response($request, $response, $response->code);
                }
            }
            if ($request->has('resource_id')) {
                if ($request->partner->hasThisResource($request->resource_id, 'Handyman')) {
                    $job->resource_id = $request->resource_id;
                    $job->update();
                    return api_response($request, $job, 200);
                } else {
                    return api_response($request, null, 403);
                }
            }
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
                    $item['added_by'] = trim(explode('-', $item->created_by_name)[1]);
                    removeSelectedFieldsFromModel($item);
                });
                return api_response($request, $materials, 200, ['materials' => $materials]);
            } else {
                return api_response($request, $job, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function addMaterial($partner, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'price' => 'required|numeric|min:1'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $job = $request->job;
            $material = new JobMaterial();
            $material->material_name = $request->name;
            $material->material_price = (double)$request->price;
            $material->job_id = $job->id;
            $material->created_by = $request->manager_resource->id;
            $material->created_by_name = 'Resource - ' . $request->manager_resource->profile->name;
            $material->save();
            return api_response($request, $material, 200);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateMaterial($partner, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'material_id' => 'required|numeric',
                'name' => 'required|string',
                'price' => 'required|numeric|min:1'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $job = $request->job;
            $material = $job->usedMaterials->where('id', (int)$request->material_id)->first();
            if ($material) {
                $material->material_name = $request->name;
                $material->material_price = $request->price;
                $material->updated_by = $request->manager_resource->id;
                $material->updated_by_name = 'Resource - ' . $request->manager_resource->profile->name;
                $material->update();
                return api_response($request, null, 200);
            }
            return api_response($request, null, 403);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }


}