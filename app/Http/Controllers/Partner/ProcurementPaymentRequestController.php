<?php namespace App\Http\Controllers\Partner;

use App\Models\Procurement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sheba\Business\ProcurementPaymentRequest\Creator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Sheba\Business\ProcurementPaymentRequest\Updater;
use Sheba\Dal\ProcurementPaymentRequest\Model;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class ProcurementPaymentRequestController extends Controller
{
    use ModificationFields;

    public function index($partner, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $creator->setProcurement($procurement)->setBid($bid);
            $payment_request_list = $creator->getAll();
            return api_response($request, $payment_request_list, 200, ['payment_request_list' => $payment_request_list]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function paymentRequest($partner, $procurement, $bid, Request $request, Creator $creator, ProcurementRepositoryInterface $procurement_repository)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'short_description' => 'required|string'
        ]);
        $this->setModifier($request->manager_resource);
        /** @var Procurement $procurement */
        $procurement = $procurement_repository->find($procurement);
        $procurement->calculate();
        if ((double)$request->amount > $procurement->due) return api_response($request, null, 420, ["message" => "Your total requested amount exceeded the bidding price."]);
        $payment_request = $creator->setProcurement($procurement)->setBid($bid)->setAmount($request->amount)->setShortDescription($request->short_description)->paymentRequestCreate();
        return api_response($request, $payment_request, 200, ['id' => $payment_request->id]);
    }

    public function show($partner, $procurement, $bid, $payment_request, Request $request, Creator $creator)
    {
        try {
            $creator->setProcurement($procurement)->setBid($bid)->setPaymentRequest($payment_request);
            $payment_request_details = $creator->getPaymentRequestData();
            return api_response($request, $payment_request_details, 200, ['payment_request_details' => $payment_request_details]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateStatus($partner, $procurement, $bid, $payment_request, ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, [
                'status' => 'required|string'
            ]);
            $this->setModifier($request->manager_resource);
            $updater->setProcurement($procurement)->setBid($bid)->setPaymentRequest($procurement_payment_request_repository->find($payment_request))->setStatus($request->status);
            $updater->updateStatus();
            return api_response($request, null, 200);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
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