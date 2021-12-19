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
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;

class InspectionController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            list($offset, $limit) = calculatePagination($request);
            $inspections = Inspection::with(['formTemplate', 'inspectionSchedule.inspections'])
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC');
            $inspection_lists = [];
            if ($request->filled('filter') && $request->filter === 'process') {##Ongoing
                $inspections = $inspections->where(function ($query) {
                    $query->where('status', '<>', 'closed')
                        ->where('status', '<>', 'cancelled')
                        ->where('created_at', '>=', Carbon::today()->toDateString() . ' 00:00:00');
                })->orderBy('start_date')->skip($offset)->limit($limit);

                if ($request->filled('inspection_form')) {
                    $inspections = $inspections->whereHas('formTemplate', function ($query) use ($request) {
                        $query->where('id', $request->inspection_form);
                    });
                }
                if ($request->filled('type')) {
                    $inspections = $inspections->where('type', $request->type);
                }
                foreach ($inspections->get() as $inspection) {
                    $next_start_date = $inspection->getNextStartDate();
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form_id' => $inspection->formTemplate ? $inspection->formTemplate->id : null,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'type' => $inspection->type,
                        'start_date' => $inspection->start_date->toDateTimeString(),
                        'next_start_date' => $next_start_date ? $next_start_date->format('l, j M') : null,
                    ];
                    array_push($inspection_lists, $inspection);
                }
            } elseif ($request->filled('filter') && $request->filter === 'open') {##Schedule
                $inspections = $inspections->where('status', 'open')->skip($offset)->limit($limit);

                if ($request->filled('inspection_form')) {
                    $inspections = $inspections->where('form_template_id', $request->inspection_form);
                }

                if ($request->filled('type')) {
                    $inspections->whereHas('vehicle', function ($query) use ($request) {
                        $query->whereHas('basicInformations', function ($query) use ($request) {
                            $query->where('type', $request->type);
                        });
                    });
                }

                foreach ($inspections->get() as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form_id' => $inspection->formTemplate ? $inspection->formTemplate->id : null,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $member->profile->name,
                        'type' => $inspection->type,
                        'schedule' => Carbon::parse($inspection->start_date)->format('j M'),
                        'id_due' => Carbon::today()->greaterThan($inspection->start_date) ? 1 : 0,
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
                $inspections = $inspections->where('status', 'closed')->skip($offset)->limit($limit);##History
                if ($request->filled('inspection_form')) {
                    $inspections = $inspections->whereHas('formTemplate', function ($query) use ($request) {
                        $query->where('id', $request->inspection_form);
                    });
                }
                if ($request->filled('type')) {
                    $inspections = $inspections->where('type', $request->type);
                }

                $start_date = $request->filled('start_date') ? $request->start_date : null;
                $end_date = $request->filled('end_date') ? $request->end_date : null;
                if ($start_date && $end_date) {
                    $inspections->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
                }

                foreach ($inspections->get() as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $next_start_date = $inspection->getNextStartDate();
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form_id' => $inspection->formTemplate ? $inspection->formTemplate->id : null,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $inspection->member->profile->name,
                        'type' => $inspection->type,
                        'failed_items' => $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                        'submitted' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,
                        'next_start_date' => $next_start_date ? $next_start_date->format('l, j M') : null,
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
            $inspection_items = $inspection_item_repository->getAllByBusiness((int)$business->id)
                ->whereBetween('created_at', [Carbon::now()->subDays(7)->toDateTimeString(), Carbon::now()->toDateTimeString()])
                ->where('input_type', 'radio')
                ->select(['id', 'status', 'result'])
                ->get();
            $failed_items = $this->getFailedItems($inspection_items);
            $failure_percent_in_last_seven_days = $inspection_items->count() > 0 ? ($failed_items->count() / $inspection_items->count()) * 100 : 0;
            $inspection_items = $inspection_item_repository->getAllByBusiness((int)$business->id)
                ->whereBetween('created_at', [Carbon::now()->subDays(14)->toDateTimeString(), Carbon::now()->subDays(7)->toDateTimeString()])
                ->where('input_type', 'radio')
                ->select(['id', 'status', 'result'])
                ->get();
            $failed_items = $this->getFailedItems($inspection_items);
            $failure_percent_before_last_seven_days = $inspection_items->count() > 0 ? ($failed_items->count() / $inspection_items->count()) * 100 : 0;
            $difference = $failure_percent_before_last_seven_days - $failure_percent_in_last_seven_days;
            if (count($inspection_lists) > 0) return api_response($request, $inspection_lists, 200, [
                'inspection_lists' => $inspection_lists,
                'over_due' => 0,
                'item_failure_rate' => round($failure_percent_in_last_seven_days, 2),
                'is_rate_change_upwords' => $failure_percent_in_last_seven_days >= $failure_percent_before_last_seven_days ? 1 : 0,
                'item_failure_rate_change' => $difference >= 0 ? round($difference, 2) : round($difference * (-1), 2)
            ]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getFailedItems($items)
    {
        return $items->filter(function ($item) {
            return preg_match('/(?i)(fail)/', $item->result);
        });
    }

    public function getChildrenInspections($business, $inspection, Request $request, InspectionRepositoryInterface $inspection_repository)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:ongoing,history',
            ]);
            $member = $request->manager_member;
            $inspection = $inspection_repository->where('business_id', $business)->where('id', $inspection)->first();
            $inspections = $inspection_repository->where('inspection_schedule_id', $inspection->inspection_schedule_id);
            if ($request->filter == 'ongoing') $inspections = $inspections->whereIn('status', ['process', 'open'])->get();
            else $inspections = $inspections->where('status', 'closed')->get();
            $inspections->load(['vehicle' => function ($q) {
                $q->with(['basicInformations', 'businessDepartment']);
            }, 'formTemplate']);
            $inspection_lists = [];
            foreach ($inspections as $inspection) {
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                $inspection = [
                    'id' => $inspection->id,
                    'inspection_form_id' => $inspection->formTemplate ? $inspection->formTemplate->id : null,
                    'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                    'inspector' => $member->profile->name,
                    'type' => $inspection->type,
                    'schedule' => Carbon::parse($inspection->start_date)->format('j M'),
                    'id_due' => Carbon::today()->greaterThan($inspection->start_date) ? 1 : 0,
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information ? $basic_information->model_name : null,
                        'model_year' => $basic_information ? Carbon::parse($basic_information->model_year)->format('Y') : null,
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information ? $basic_information->type : null,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($inspection_lists, $inspection);
            }
            if (count($inspections) > 0) return api_response($request, $inspection_lists, 200, ['inspections' => $inspection_lists]);
            else  return api_response($request, null, 404);
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
                    'status' => $this->getStatus($inspection_item, $inspection_item->status),
                    'issue_id' => $inspection_item->status == 'issue_created' ? $inspection_item->issue->id : null,
                    'is_acknowledge' => $inspection_item->status == 'acknowledged' ? 1 : 0,
                    'acknowledgment_note' => $inspection_item->acknowledgment_note,
                ];
                array_push($items, $item);
            }
            $vehicle = $inspection->vehicle;
            $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
            $driver = $vehicle->driver;
            $next_start_date = $inspection->getNextStartDate();
            $inspection = [
                'id' => $inspection->id,
                'title' => $inspection->title,
                'short_description' => $inspection->short_description,
                'long_description' => $inspection->long_description,
                'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                'inspector' => $inspection->member->profile->name,
                'inspector_pic' => $inspection->member->profile->pro_pic,
                'failed_items' => $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->count(),
                'submission_note' => $inspection->submission_note,
                'submitted_date' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,
                'type' => $inspection->type,
                'status' => $inspection->status,
                'created_at' => $inspection->created_at ? $inspection->created_at->toDateTimeString() : null,
                'next_start_date' => $next_start_date ? $next_start_date->format('l, j M') : null,
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

    private function getStatus($item, $status)
    {
        if ($status === 'open') {
            return 'Pending';
        } elseif ($status === 'issue_created') {
            return 'has_issue';
        } else {
            return 'Acknowledged';
        }
    }

    public function individualInspection($member, Request $request)
    {
        try {
            $business_member = $request->business_member;
            $business = $request->business;
            $member = $request->member;
            $this->setModifier($member);
            list($offset, $limit) = calculatePagination($request);
            if (!$business_member->is_super) {
                $inspections = Inspection::with('formTemplate')->where('member_id', $member->id)->orderBy('id', 'DESC');
            } else {
                $inspections = Inspection::with('formTemplate')->where('business_id', $business_member->business_id)->orderBy('id', 'DESC');
            }

            $inspection_lists = [];
            if ($request->filled('filter') && $request->filter === 'open') {
                $inspections = $inspections->where('status', 'open')->skip($offset)->limit($limit);
                if ($request->filled('inspection_form')) {
                    $inspections = $inspections->whereHas('formTemplate', function ($query) use ($request) {
                        $query->where('id', $request->inspection_form);
                    });
                }
                if ($request->filled('type')) {
                    $inspections->whereHas('vehicle', function ($query) use ($request) {
                        $query->whereHas('basicInformations', function ($query) use ($request) {
                            $query->where('type', $request->type);
                        });
                    });
                }

                foreach ($inspections->get() as $inspection) {
                    $vehicle = $inspection->vehicle;
                    $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                    $inspection = [
                        'id' => $inspection->id,
                        'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                        'inspector' => $member->profile->name,
                        'inspector_pro_pic' => $member->profile->pro_pic,
                        'type' => $inspection->type,
                        'schedule' => Carbon::parse($inspection->start_date)->format('j M'),
                        'id_due' => Carbon::today()->greaterThan($inspection->start_date) ? 1 : 0,
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
                $inspections = $inspections->where('status', 'closed')->skip($offset)->limit($limit);
                if ($request->filled('inspection_form')) {
                    $inspections = $inspections->whereHas('formTemplate', function ($query) use ($request) {
                        $query->where('id', $request->inspection_form);
                    });
                }

                $start_date = $request->filled('start_date') ? $request->start_date : null;
                $end_date = $request->filled('end_date') ? $request->end_date : null;
                if ($start_date && $end_date) {
                    $inspections->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
                }

                foreach ($inspections->get() as $inspection) {
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
            /** @var Creator $creation_class */
            $creation_class = $create_processor->setType($request->schedule_type)->getCreationClass();
            $inspection = $creation_class->setData($request->all())->setBusiness($request->business)->create();
            return api_response($request, null, 200, ['id' => $inspection->id]);
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
            /** @var Inspection $inspection */
            $inspection = $inspection_repository->where('id', $inspection)->where('business_id', (int)$business)->first();
            if (!$inspection) return api_response($request, $inspection, 404);
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

    public function inspectionForms($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $inspections = Inspection::with('formTemplate')
                ->where('business_id', $business->id)
                ->orderBy('id', 'DESC')->get();

            $form_lists = collect();
            foreach ($inspections as $inspection) {
                $inspection_form = $inspection->formTemplate;
                $form_lists->push([
                    'id' => $inspection_form ? $inspection_form->id : null,
                    'title' => $inspection_form ? $inspection_form->title : null,
                ]);
            }
            if (count($form_lists) > 0) return api_response($request, $form_lists, 200, ['form_lists' => $form_lists->unique()->values()]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}