<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Sheba\ResellerPayment\Exceptions\UnauthorizedRequestFromMORException;
use App\Sheba\ResellerPayment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentServiceController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function getPaymentGateway(Request $request): JsonResponse
    {
        $completion = $request->query('completion');
        $banner     = $request->query('banner');
        $header_message = 'সর্বাধিক ব্যবহৃত';
        $partnerId = $request->partner->id;

        $pgwData = $this->paymentService->setPartner($request->partner)->getPaymentGateways($completion, $header_message, $partnerId, $banner);
        return api_response($request, null, 200, ['data' => $pgwData]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPaymentServiceCharge(Request $request): JsonResponse
    {
        $partnerId = $request->partner->id;
        $data = $this->paymentService->getServiceCharge($partnerId);
        return api_response($request, null, 200, ['data' => $data]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storePaymentServiceCharge(Request $request)
    {
        try {
            $this->validate($request, [
                "current_percentage" => "required | numeric"
            ]);

            $partnerId = $request->partner->id;
            $currentPercentage = $request->current_percentage;

            $this->paymentService->storeServiceCharge($partnerId, $currentPercentage);

            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function emiInformationForManager(Request $request)
    {
        try {
            $partner = $request->partner;

            $this->validate($request, ['amount' => 'required|numeric|min:' . config('emi.manager.minimum_emi_amount')]);
            $amount       = $request->amount;

            $emi_data = $this->paymentService->getEmiInfoForManager($partner, $amount);

            return api_response($request, null, 200, ['price' => $amount, 'info' => $emi_data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function bannerAndStatus(Request $request, PaymentService $paymentService)
    {
        $partner = $request->partner;
        $data = $paymentService->setPartner($partner)->getStatusAndBanner();
        return api_response($request, null, 200, ['data' => $data]);
    }

    public function getPaymentGatewayDetails(Request $request, PaymentService $paymentService)
    {
        $this->validate($request, [
            'key' => 'required|in:'.implode(',', config('reseller_payment.available_payment_gateway_keys'))
        ]);
        $partner = $request->partner;
        $detail = $paymentService->setPartner($partner)->setKey($request->key)->getPGWDetails();
        return api_response($request, null, 200, ['data' => $detail]);
    }

    /**
     * @throws UnauthorizedRequestFromMORException
     */
    public function sendNotificationOnStatusChange(Request $request, PaymentService $paymentService)
    {
        $this->validate($request, [
            'key' => 'required|in:'. implode(',', config('reseller_payment.available_payment_gateway_keys')),
            'new_status' => 'required|in:processing,verified,rejected'
        ]);
        if (($request->header('access-key')) !== config('reseller_payment.mor_access_token'))
            throw new UnauthorizedRequestFromMORException();


        //$paymentService->setKey($request->key)->setStatus($request->status)->sendNotificationOnStatusChange();
        return api_response($request, null, 200, ["message" => 'Notification sent successfully']);

    }
}