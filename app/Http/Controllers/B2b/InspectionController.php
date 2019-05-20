<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $inspections = Inspection::with('formTemplate')->where('business_id', $business->id)->orderBy('id', 'DESC')->get();
            #dd($inspections);
            $inspection_lists = [];
            foreach ($inspections as $inspection) {
                #dd($inspection->vehicle->businessDepartment);
                #dd($inspection->inspectionItems()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count());
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations;
                $inspection = [
                    'id' => $inspection->id,
                    'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                    'inspector' => $inspection->member->profile->name,
                    'failed_items' => $inspection->inspectionItems()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                    'submitted' => Carbon::parse($inspection->next_start_date)->format('j M'),
                    'next_start_date' => Carbon::parse($inspection->next_start_date)->format('l, j M'),
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information->model_name,
                        'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information->type,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($inspection_lists, $inspection);
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $inspection, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $inspection = Inspection::with('inspectionItems', 'formTemplate', 'vehicle.basicInformations')
                ->where('id', (int)$inspection)->first();
            if (!$inspection) return api_response($request, null, 404);

            $inspection_items = $inspection->inspectionItems()->where('inspection_id', $inspection->id)->select('id', 'title', 'result')->get();
            $items = [];
            foreach ($inspection_items as $inspection_item){
                $item = [
                    'id' => $inspection_item->id,
                    'title' => $inspection_item->title,
                    'result' => $inspection_item->result,
                ];
                array_push($items, $item);
            }
            $vehicle = $inspection->vehicle;
            $basic_information = $vehicle->basicInformations;
            $driver = $vehicle->driver;
            $inspection = [
                'id' => $inspection->id,
                'title' => $inspection->title,
                'short_description' => $inspection->short_description,
                'long_description' => $inspection->long_description,
                'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                'inspector' => $inspection->member->profile->name,
                'failed_items' => $inspection->inspectionItems()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                'submitted' => Carbon::parse($inspection->next_start_date)->format('l, j M'),#Dummy
                'submitted_from' => 'Fleetio Web App',#Dummy
                'inspection_items' => $items,
                'vehicle' => [
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                ],
            ];
            return api_response($request, $inspection, 200, ['inspection' => $inspection]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function ongoingInspections($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $inspections = Inspection::with('formTemplate')->where('business_id', $business->id)->orderBy('id', 'DESC')->get();


            /*if ($request->has('status')) {
                $members->where(function ($query) use ($request) {
                    $query->whereHas('businessMember.role.businessDepartment', function ($query) use ($request) {
                        $query->where('name', $request->department);
                    });
                });
            }*/
            $inspection_lists = [];
            foreach ($inspections as $inspection) {
                $inspection = [
                    'id' => $inspection->id,
                    'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                    'type' => $inspection->type,
                    'next_start_date' => Carbon::parse($inspection->next_start_date)->format('l, j M'),
                ];
                array_push($inspection_lists, $inspection);
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function individualInspectionHistory($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);

            $inspections = Inspection::with('formTemplate')->where('business_id', $business->id)->where('member_id', $member->id)->orderBy('id', 'DESC')->get();
            #dd($inspections);
            $inspection_lists = [];
            foreach ($inspections as $inspection) {
                #dd($inspection->vehicle->businessDepartment);
                #dd($inspection->inspectionItems()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count());
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations;
                $inspection = [
                    'id' => $inspection->id,
                    'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                    'inspector' => $member->profile->name,
                    'failed_items' => $inspection->inspectionItems()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                    'submitted' => Carbon::parse($inspection->next_start_date)->format('j M'),
                    'next_start_date' => Carbon::parse($inspection->next_start_date)->format('l, j M'),
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information->model_name,
                        'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information->type,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($inspection_lists, $inspection);
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}