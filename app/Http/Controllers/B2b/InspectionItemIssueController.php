<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionItemIssue;
use Carbon\Carbon;
use Sheba\Business\Inspection\Creator;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Repositories\Business\InspectionRepository;

class InspectionItemIssueController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $inspection_item_issues = InspectionItemIssue::with('inspectionItem.inspection.vehicle.basicInformation')->orderBy('id', 'DESC')->get();
            dd($inspection_item_issues);
            $issue_lists = [];
            foreach ($inspection_item_issues as $issue){
                $vehicle = $issue->inspectionItem->inspection->vehicle;
                $basic_information = $vehicle->basicInformations;
             $issue = [
                 'id' => $issue->inspectionItem->id,
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
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}