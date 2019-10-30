<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ProcurementOrder\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Business\RfqOrderRepository;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;

class RfqOrderController extends Controller
{
    use ModificationFields;

    public function show($business, $procurement, $bid, Request $request, RfqOrderRepository $rfq_order_repository)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            if (!$procurement) {
                return api_response($request, null, 404, ["message" => "Procurement Not found."]);
            } else {
                /** @var Bid $bid */
                $bid = Bid::findOrFail((int)$bid);
                if (!$bid) {
                    return api_response($request, null, 404, ["message" => "Bid Not found."]);
                } else {
                    $rfq_order_details = $rfq_order_repository->setProcurement($procurement)->setBid($bid)->formatData();
                    return api_response($request, $rfq_order_details, 200, ['order_details' => $rfq_order_details]);
                }
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function paymentRequest($business, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                'note' => 'sometimes|string',
                'short_description' => 'sometimes|string',
            ]);
            $this->setModifier($request->manager_member);
            $procurement = Procurement::findOrFail((int)$procurement);
            if (!$procurement) {
                return api_response($request, null, 404, ["message" => "Procurement Not found."]);
            } else {
                /** @var Bid $bid */
                $bid = Bid::findOrFail((int)$bid);
                if (!$bid) {
                    return api_response($request, null, 404, ["message" => "Bid Not found."]);
                } else {
                    $creator = $creator->setProcurement($procurement)
                        ->setBid($bid)
                        ->setAmount($request->amount)
                        ->setShortDescription($request->amount)
                        ->setNote($request->note);
                    $payment_request = $creator->paymentRequestCreate();
                    return api_response($request, $payment_request, 200, ['id' => $payment_request->id]);
                }
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
}