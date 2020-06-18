<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Procurement;
use App\Sheba\Business\Procurement\ProcurementOrder;
use App\Sheba\Business\Procurement\Updater;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\DocBlock\Description;
use Sheba\Business\Procurement\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;

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
            $procurement = $creator->getProcurement($procurement)->getBid();

            $order_timelines = $creator->formatTimeline();

            return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function showProcurementOrder($partner, $procurement, $bid, Request $request, ProcurementOrder $procurement_order, BidRepositoryInterface $bid_repository)
    {
        $bid = $bid_repository->find((int)$bid);
        if (!$bid) return api_response($request, null, 404);
        $rfq_order_details = $procurement_order->setProcurement($procurement)->setBid($bid)->orderDetails();
        return api_response($request, null, 200, ['order_details' => $rfq_order_details]);
    }

    public function orderBill($partner, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            $procurement->calculate();
            $rfq_order_bill['total_price'] = $procurement->getActiveBid()->price;
            $rfq_order_bill['paid'] = $procurement->paid;
            $rfq_order_bill['due'] = $procurement->due;
            return api_response($request, $rfq_order_bill, 200, ['rfq_order_bill' => $rfq_order_bill]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}