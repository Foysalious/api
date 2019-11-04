<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Sheba\Business\Procurement\Updater;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Procurement\Creator;
use Sheba\ModificationFields;

class ProcurementController extends Controller
{
    use ModificationFields;

    public function updateStatus($partner, $procurement, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, [
                'status' => 'required|string',
            ]);
            $this->setModifier($request->manager_resource);
            $procurement = Procurement::find((int)$procurement);
            if (!$procurement) {
                return api_response($request, null, 404);
            } else {
                $updater->setProcurement($procurement)->setStatus($request->status)->updateStatus();
                return api_response($request, null, 200);
            }
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

    public function orderTimeline($partner, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = $creator->getProcurement($procurement);

            $order_timelines = $creator->formatTimeline();

            return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}