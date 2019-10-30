<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\ProcurementPaymentRequest\Updater;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class ProcurementPaymentRequestController extends Controller
{
    use ModificationFields;

    public function updatePaymentRequest($business, $procurement, $bid, $payment_request, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, [
                'note' => 'sometimes|string',
                'status' => 'sometimes|string'
            ]);
            $this->setModifier($request->manager_member);
            $updater->setProcurement($procurement)->setBid($bid);
            $procurement = $updater->getProcurement();
            if (!$procurement) {
                return api_response($request, null, 404, ["message" => "Procurement Not found."]);
            } else {
                $bid = $updater->getBid();
                if (!$bid) {
                    return api_response($request, null, 404, ["message" => "Bid Not found."]);
                } else {
                    $updater = $updater->setPaymentRequest($payment_request)->setNote($request->note)
                        ->setStatus($request->status);
                    $payment_request = $updater->paymentRequestUpdate();
                    return api_response($request, $payment_request, 200);
                }
            }

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}