<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Inspection\CreateProcessor;
use Sheba\Business\Inspection\Creator;
use Sheba\Business\Inspection\Submission;
use Sheba\Business\Inspection\SubmissionValidator;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;

class InspectionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $inspections = Inspection::with('formTemplate')
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC');

            $inspection_lists = [];
            if ($request->has('filter') && $request->filter === 'process') {
                $inspections = $inspections->where(function ($query) {
                    $query->where('status', '<>', 'closed')
                        ->where('status', '<>', 'cancelled')
                        ->where('created_at', '>=', Carbon::today()->toDateString() . ' 00:00:00');
                })->get();
                foreach ($inspections as $inspection) {
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form_id' => $inspection->formTemplate ? $inspection->formTemplate->id : null,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'type' => $inspection->type,
                        'next_start_date' => Carbon::parse($inspection->next_start_date)->format('l, j M'),
                    ];
                    array_push($inspection_lists, $inspection);
                }
            } elseif ($request->has('filter') && $request->filter === 'open') {
                $inspections = $inspections->where('status', 'open')->get();
                foreach ($inspections as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $member->profile->name,
                        'schedule' => Carbon::parse($inspection->start_date)->format('j M'),
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
            } else {
                $inspections = $inspections->where('status', 'closed')->get();
                foreach ($inspections as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $inspection->member->profile->name,
                        'failed_items' => $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                        'submitted' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,
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
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $inspection, Request $request, InspectionRepositoryInterface $inspection_repository)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $inspection = $inspection_repository->find($inspection);
            if (!$inspection) return api_response($request, null, 404);

            $inspection_items = $inspection->items;
            $items = [];
            foreach ($inspection_items as $inspection_item) {
                $item = [
                    'id' => $inspection_item->id,
                    'title' => $inspection_item->title,
                    'result' => $inspection_item->result,
                    'short_description' => $inspection_item->short_description,
                    'long_description' => $inspection_item->long_description,
                    'input_type' => $inspection_item->input_type,
                    'variables' => json_decode($inspection_item->variables),
                    'comment' => $inspection_item->comment,
                    'status' => $inspection_item->status,
                    'acknowledgment_note' => $inspection_item->acknowledgment_note,
                ];
                array_push($items, $item);
            }
            $vehicle = $inspection->vehicle;
            $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
            $driver = $vehicle->driver;
            $inspection = [
                'id' => $inspection->id,
                'title' => $inspection->title,
                'short_description' => $inspection->short_description,
                'long_description' => $inspection->long_description,
                'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                'inspector' => $inspection->member->profile->name,
                'inspector_pic' => $inspection->member->profile->pro_pic,
                'failed_items' => $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                'submitted_date' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,
                'status' => $inspection->status,
                'inspection_items' => $items,
                'vehicle' => [
                    'vehicle_model' => $basic_information ? $basic_information->model_name : null,
                    'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information ? $basic_information->type : null,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                ],
            ];
            return api_response($request, $inspection, 200, ['inspection' => $inspection]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function individualInspection($member, Request $request)
    {
        try {
            $member = $request->member;
            $this->setModifier($member);

            $inspections = Inspection::with('formTemplate')->where('member_id', $member->id)->orderBy('id', 'DESC');
            $inspection_lists = [];
            if ($request->has('filter') && $request->filter === 'open') {
                $inspections = $inspections->where('status', 'open')->get();
                foreach ($inspections as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $member->profile->name,
                        'schedule' => Carbon::parse($inspection->start_date)->format('j M'),
                        'vehicle' => [
                            'vehicle_model' => $basic_information ? $basic_information->model_name : null,
                            'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                            'status' => $vehicle->status,
                            'vehicle_type' => $basic_information ? $basic_information->type : null,
                            'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                        ],
                    ];
                    array_push($inspection_lists, $inspection);
                }
            } else {
                $inspections = $inspections->where('status', 'closed')->get();
                foreach ($inspections as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $member->profile->name,
                        'failed_items' => $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                        'submitted' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,
                        'next_start_date' => Carbon::parse($inspection->next_start_date)->format('l, j M'),
                        'vehicle' => [
                            'vehicle_model' => $basic_information ? $basic_information->model_name : null,
                            'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                            'status' => $vehicle->status,
                            'vehicle_type' => $basic_information ? $basic_information->type : null,
                            'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                        ],
                    ];
                    array_push($inspection_lists, $inspection);
                }
            }
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, ['inspection_lists' => $inspection_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($business, Request $request, CreateProcessor $create_processor)
    {
        try {
            $this->setModifier($request->manager_member);
            $request->merge(['member_id' => $request->manager_member->id]);
            /** @var Creator $creation_class */
            $creation_class = $create_processor->setType($request->schedule_type)->getCreationClass();
            $creation_class->setData($request->all())->setBusiness($request->business)->create();
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function edit($business, $inspection, Request $request, InspectionRepositoryInterface $inspection_repository)
    {
        try {
            $this->setModifier($request->manager_member);
            $inspection = $inspection_repository->find($inspection);
            $inspection_repository->update($inspection, [
                'title' => $request->title,
                'short_description' => $request->short_description,
            ]);
            return api_response($request, $inspection, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function submit($business, $inspection, Request $request, SubmissionValidator $submission_validator, InspectionRepositoryInterface $inspection_repository, Submission $submission)
    {
        try {
            $this->validate($request, [
                'submission_note' => 'required|string',
                'items' => 'required|string',
            ]);
            $member = $request->manager_member;
            $this->setModifier($member);
            $inspection = $inspection_repository->find($inspection);
            $inspection->load('items');
            $submission_validator->setBusinessMember($request->business_member)->setInspection($inspection)->setItemResult(json_decode($request->items));
            if (!$submission_validator->hasAccess()) return api_response($request, null, 403);
            if ($submission_validator->hasError()) return api_response($request, null, 400, ['message' => $submission_validator->getErrorMessage()]);
            $submission->setData($request->all())->setInspection($inspection)->submit();
            return api_response($request, $inspection, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}