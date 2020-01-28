<?php namespace App\Http\Controllers\B2b;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sheba\Business\ProcurementPaymentRequest\Creator;
use Sheba\Business\ProcurementPaymentRequest\Updater;
use App\Http\Controllers\Controller;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class ProcurementPaymentRequestController extends Controller
{
    use ModificationFields;

    public function index($business, $procurement, $bid, Request $request, Creator $creator)
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

    public function updatePaymentRequest($business, $procurement, $bid, $payment_request, ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'note' => 'sometimes|string',
            'status' => 'sometimes|string'
        ]);
        $this->setModifier($request->manager_member);
//        $payment_request = $updater->setProcurement($procurement)->setBid($bid)->setPaymentRequest($procurement_payment_request_repository->find($payment_request))
//            ->setNote($request->note)->setStatus($request->status)->paymentRequestUpdate();
        return api_response($request, $payment_request, 200);
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
}