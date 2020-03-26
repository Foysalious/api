<?php namespace App\Http\Controllers\B2b;

use App\Models\Bid;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Sheba\Business\Procurement\WorkOrderDataGenerator;
use Sheba\Business\ProcurementPaymentRequest\Creator;
use Sheba\Business\ProcurementPaymentRequest\Updater;
use App\Http\Controllers\Controller;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Throwable;

class ProcurementPaymentRequestController extends Controller
{
    use ModificationFields;

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function index($business, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $creator->setProcurement($procurement)->setBid($bid);
            $payment_request_list = $creator->getAll();
            return api_response($request, $payment_request_list, 200, ['payment_request_list' => $payment_request_list]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $procurement
     * @param $bid
     * @param $payment_request
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function show($partner, $procurement, $bid, $payment_request, Request $request, Creator $creator)
    {
        try {
            $creator->setProcurement($procurement)->setBid($bid)->setPaymentRequest($payment_request);
            $payment_request_details = $creator->getPaymentRequestData();
            return api_response($request, $payment_request_details, 200, ['payment_request_details' => $payment_request_details]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param $payment_request
     * @param ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updatePaymentRequest($business, $procurement, $bid, $payment_request, ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repository, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'note' => 'sometimes|string', 'status' => 'sometimes|string'
        ]);
        $this->setModifier($request->manager_member);
        $updater->setProcurement($procurement)->setBid($bid)->setPaymentRequest($procurement_payment_request_repository->find($payment_request))->setNote($request->note)->setStatus($request->status)->paymentRequestUpdate();
        if ($updater->getErrorMessage()) return api_response($request, null, 403, ['message' => $updater->getErrorMessage()]);
        return api_response($request, $payment_request, 200);
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param $payment_request
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     */
    public function downloadPdf($business, $procurement, $bid, $payment_request, Request $request, WorkOrderDataGenerator $data_generator)
    {
        $business = $request->business;
        $bid = Bid::findOrFail((int)$bid);
        $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();

        dd($work_order);
    }
}
