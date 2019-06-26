<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Procurement\Creator;
use Sheba\ModificationFields;

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
            $this->setModifier($request->manager_member);
            $creator->setType($request->type)->setOwner($request->business)->setTitle($request->title)->setPurchaseRequest($request->purchase_request_id)
                ->setLongDescription($request->description)->setOrderStartDate($request->order_start_date)->setOrderEndDate($request->order_end_date)
                ->setInterviewDate($request->interview_date)->setProcurementStartDate($request->tender_start_date)->setProcurementEndDate($request->tender_start_date)
                ->setItems($request->items)->setQuestions($request->questions);
            $procurement = $creator->create();
            return api_response($request, null, 200, ['id' => $procurement->id]);
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