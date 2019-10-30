<?php namespace App\Http\Controllers\Partner;


use App\Models\Bid;
use App\Models\Procurement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ProcurementPaymentRequest\Creator;
use Sheba\ModificationFields;

class ProcurementPaymentRequestController extends Controller
{
    use ModificationFields;

    public function paymentRequest($partner, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                'short_description' => 'required|string'
            ]);
            $this->setModifier($request->manager_member);
            $creator->setProcurement($procurement)->setBid($bid);
            $procurement = $creator->getProcurement();
            $bid = $creator->getBid();

            if (!$procurement || !$bid) {
                return api_response($request, null, 404, ["message" => "Procurement or Bid Not found."]);
            } else {
                $creator = $creator->setAmount($request->amount)
                    ->setShortDescription($request->short_description);
                $payment_request = $creator->paymentRequestCreate();
                return api_response($request, $payment_request, 200, ['id' => $payment_request->id]);
            }

        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Procurement or Bid Not found."]);
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