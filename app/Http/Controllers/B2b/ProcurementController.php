<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Procurement\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class ProcurementController extends Controller
{
    use ModificationFields;

    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        try {
            $this->validate($request, [
                'type' => 'required|string:in:product,service',
                'purchase_request_id' => 'sometimes|numeric',
                'title' => 'required|string',
                'description' => 'required',
                'estimated_price' => 'required|string',
                'items' => 'required|string',
                'questions' => 'required|string',
                'order_start_date' => 'sometimes|date_format:Y-m-d h:i:s',
                'order_end_date' => 'sometimes|date_format:Y-m-d h:i:s',
                'interview_date' => 'sometimes|date_format:Y-m-d h:i:s',
                'tender_start_date' => 'sometimes|date_format:Y-m-d h:i:s',
                'tender_end_date' => 'sometimes|date_format:Y-m-d h:i:s',
            ]);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('procurement.rw')) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $creator->setType($request->type)->setOwner($request->business)->setTitle($request->title)->setPurchaseRequest($request->purchase_request_id)
                ->setLongDescription($request->description)->setOrderStartDate($request->order_start_date)->setOrderEndDate($request->order_end_date)
                ->setInterviewDate($request->interview_date)->setProcurementStartDate($request->tender_start_date)->setProcurementEndDate($request->tender_start_date)
                ->setItems($request->items)->setQuestions($request->questions);
            $procurement = $creator->create();
            return api_response($request, $procurement, 200, ['id' => $procurement->id]);
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

    public function index($business, Request $request, AccessControl $access_control, ProcurementRepositoryInterface $procurement_repository)
    {
        try {
            $this->validate($request, [
                'status' => 'sometimes|string',
            ]);
            $access_control->setBusinessMember($request->business_member);
            if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $business = $request->business;
            $procurements = $procurement_repository->ofBusiness($business->id)->select(['id', 'title', 'long_description', 'status', 'procurement_start_date', 'procurement_end_date']);
            if ($request->has('status')) $procurements->where('status', $request->status);
            $procurements = $procurements->get();
            if (count($procurements) > 0) return api_response($request, $procurements, 200, ['procurements' => $procurements]);
            else return api_response($request, null, 404);
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