<?php

namespace App\Http\Controllers\QRPayment;

use App\Http\Controllers\Controller;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use App\Sheba\QRPayment\DTO\QRGeneratePayload;
use App\Sheba\QRPayment\QRPayableGenerator;
use App\Sheba\QRPayment\QRPaymentStatics;
use App\Sheba\QRPayment\QRValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\QRPayment\Exceptions\QRException;
use Throwable;

class QRPaymentController extends Controller
{
    /**
     * @param Request $request
     * @param QRPayableGenerator $QRPayableAdapter
     * @return JsonResponse
     * @throws QRException|PosOrderServiceServerError
     */
    public function generateQR(Request $request, QRPayableGenerator $QRPayableAdapter): JsonResponse
    {
        $this->validate($request, QRPaymentStatics::getValidationForQrGenerate());
        $partner = $request->auth_user->getPartner();
        $data = array_only($request->all(), QRPaymentStatics::qrGenerateKeys());
        $data = new QRGeneratePayload($data);
        $qr_payable = $QRPayableAdapter->setPartner($partner)->setData($data)->getQrPayable();

        return http_response($request, null, 200, ["qr" => [
            "qr_code" => $qr_payable->getQRString(),
            "qr_id" => $qr_payable->qr_id
        ]]);
    }

    /**
     * @param $payment_method
     * @param Request $request
     * @param QRValidator $validator
     * @return JsonResponse
     * @throws QRException
     * @throws AlreadyCompletingPayment
     * @throws Throwable
     */
    public function validatePayment($payment_method, Request $request, QRValidator $validator): JsonResponse
    {
        $this->validate($request, QRPaymentStatics::getValidationForValidatePayment());
        $validator->setRequest($request->all())->setGateway($payment_method)
            ->setQrId($request->qr_id)->setAmount($request->amount)->setMerchantId($request->merchant_id)->complete();
        return http_response($request, null, 200);
    }
}