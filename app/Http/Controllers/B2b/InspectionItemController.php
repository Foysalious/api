<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\InspectionItem\Creator;
use Sheba\Business\InspectionItem\Updater;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;

class InspectionItemController extends Controller
{
    use ModificationFields;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $inspections = Inspection::where('business_id', $business->id)
                ->orderBy('id', 'DESC')->get();

            $inspection_items = [];
            foreach ($inspections as $inspection) {
                $items = $inspection->items()->where('input_type', 'radio')->where('result', 'LIKE', '%failed%')->get();
                array_push($inspection_items, $items);
            }
            $item_lists = [];
            foreach (array_flatten($inspection_items) as $item) {
                $inspection = $item->inspection;
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations ? $vehicle->basicInformations : null;
                $item = [
                    'id' => $item->id,
                    'date' => $item->updated_at->format('M d, Y'),
                    'time' => $item->updated_at->format('h:i a'),
                    'title' => $item->title,
                    'short_description' => $item->short_description,
                    'long_description' => $item->long_description,
                    'input_type' => $item->input_type,
                    'variables' => json_decode($item->variables),
                    'result' => $item->result,
                    'comment' => $item->comment,
                    'status' => $this->getStatus($item, $item->status),
                    'acknowledgment_note' => $item->acknowledgment_note,

                    'inspection_id' => $inspection->id,
                    'inspection_status' => $inspection->status,
                    'inspection_form_title' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                    'inspection_form_short_description' => $inspection->formTemplate ? $inspection->formTemplate->short_description : null,
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
                array_push($item_lists, $item);
            }

            if (count($item_lists) > 0) return api_response($request, $item_lists, 200, ['item_lists' => $item_lists]);
            else  return api_response($request, null, 404);
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
            return 'Issue #'.$item->issue->id;
        } else {
            return 'Acknowledged';
        }
    }

    public function edit($business, $inspection, $item, Request $request, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'short_description' => 'required',
                'type' => 'required|string|in:text,radio,number',
                'is_required' => 'required|numeric|in:0,1',
                'instructions' => 'required|string',
            ]);
            $this->setModifier($request->manager_member);
            $inspection_item = $inspection_item_repository->find($item);
            $inspection_item_repository->update($inspection_item, [
                'title' => $request->title,
                'short_description' => $request->short_description,
                'long_description' => $request->instructions,
                'input_type' => $request->type,
                'variables' => json_encode(['is_required' => (int)$request->is_required]),
            ]);
            return api_response($request, $inspection_item, 200);
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

    public function destroy($business, $inspection, $item, Request $request, InspectionItemRepositoryInterface $inspection_item_repository)
    {
        try {
            $this->setModifier($request->manager_member);
            $inspection_item_repository->delete($item);
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

    public function store($business, $inspection, Request $request, Creator $creator, InspectionRepositoryInterface $inspection_repository)
    {
        try {
            $this->validate($request, ['variables' => 'required|string']);
            $this->setModifier($request->manager_member);
            $creator->setData($request->all())->setInspection($inspection_repository->find($inspection))->create();
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

    public function acknowledge($business, $inspection, $item, Request $request, InspectionItemRepositoryInterface $inspection_item_repository, Updater $updater)
    {
        try {
            $this->validate($request, ['note' => 'required|string']);
            $this->setModifier($request->manager_member);
            $inspection_item = $inspection_item_repository->find($item);
            if ($inspection_item->status != 'open') return api_response($request, null, 400);
            $updater->setInspectionItem($inspection_item)->updateStatus('acknowledged', ['acknowledgment_note' => $request->note]);
            return api_response($request, $inspection_item, 200);
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