<?php namespace App\Http\Controllers\B2b;

use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use App\Http\Controllers\Controller;
use App\Models\InspectionItemIssue;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Attachment;
use Carbon\Carbon;

class IssueController extends Controller
{
    use ModificationFields;
    use FilesAttachment;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $inspection_item_issues = InspectionItemIssue::with('inspectionItem.inspection.vehicle.basicInformation')->orderBy('id', 'DESC')->get();
            $issue_lists = [];
            foreach ($inspection_item_issues as $issue) {
                $inspection = $issue->inspectionItem->inspection;
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations;
                $issue = [
                    'id' => $issue->id,
                    'title' => $issue->inspectionItem->title,
                    'short_description' => $issue->inspectionItem->short_description,
                    'long_description' => $issue->inspectionItem->long_description,
                    'status' => $issue->status,
                    'comment' => $issue->comment,
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information->model_name,
                        'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information->type,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($issue_lists, $issue);
            }
            if (count($issue_lists) > 0) return api_response($request, $issue_lists, 200, ['issue_lists' => $issue_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $issue, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$issue) return api_response($request, null, 404);
            $issue_lists = [];
            $inspection = $issue->inspectionItem->inspection;
            $vehicle = $inspection->vehicle;
            $basic_information = $vehicle->basicInformations;
            $driver = $vehicle->driver;
            $issue = [
                'title' => $issue->inspectionItem->title,
                'short_description' => $issue->inspectionItem->short_description,
                'long_description' => $issue->inspectionItem->long_description,
                'status' => $issue->status,
                'comment' => $issue->comment,
                'inspector' => $inspection->member->profile->name,
                'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                'submitted' => Carbon::parse($inspection->next_start_date)->format('j, M, Y h:i:a'),

                'vehicle' => [
                    'id' => $vehicle->id,
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                ],
            ];

            if (count($issue) > 0) return api_response($request, $issue, 200, ['issue' => $issue]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeAttachment($business, $issue, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$request->hasFile('file'))
                return redirect()->back();
            $data = $this->storeAttachmentToCDN($request->file('file'));
            $attachment = $issue->attachments()->save(new Attachment($this->withBothModificationFields($data)));
            return api_response($request, $attachment, 200, ['attachment' => $attachment->file]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}