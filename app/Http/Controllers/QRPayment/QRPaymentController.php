<?php

namespace App\Http\Controllers\QRPayment;

use App\Http\Controllers\Controller;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use App\Sheba\QRPayment\QRPayment;
use App\Sheba\QRPayment\QRPaymentStatics;
use App\Sheba\QRPayment\QRValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\QRPayment\Exceptions\QRException;

class QRPaymentController extends Controller
{
    /**
     * @param Request $request
     * @param QRPayment $QRPayment
     * @return JsonResponse
     * @throws QRException|PosOrderServiceServerError
     */
    public function generateQR(Request $request, QRPayment $QRPayment): JsonResponse
    {
        $this->validate($request, QRPaymentStatics::getValidationForQrGenerate());
        $partner   = $request->partner;
        $data      = array_only($request->all(), QRPaymentStatics::qrGeenerateKeys());
        $qr_string = $QRPayment->setPartner($partner)->setData((object)($data))->generate();
        return http_response($request, null, 200, ["qr_string" => $qr_string]);
    }

    /**
     * @param $payment_method
     * @param Request $request
     * @param QRValidator $validator
     * @return JsonResponse
     * @throws QRException
     */
    public function validatePayment($payment_method, Request $request, QRValidator $validator): JsonResponse
    {
        $this->validate($request, ["qr_id" => "required"]);
        $validator->setResponse(json_encode($request->all()))->setPaymentMethod($payment_method)
            ->setQrId($request->qr_id)->complete();
        return http_response($request, null, 200);
    }
}