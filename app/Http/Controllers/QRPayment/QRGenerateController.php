<?php

namespace App\Http\Controllers\QRPayment;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Sheba\QRPayment\QRPayment;
use App\Sheba\QRPayment\QRPaymentStatics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QRGenerateController extends Controller
{
    /**
     * @param Request $request
     * @param QRPayment $QRPayment
     * @return JsonResponse
     */
    public function generate(Request $request, QRPayment $QRPayment): JsonResponse
    {
        $this->validate($request, QRPaymentStatics::getValidationForQrGenerate());
        $partner   = $request->partner;
        $data      = array_only($request->all(), ['payable_type', 'type_id', 'amount', 'customer_id', 'payment_method']);
        $qr_string = $QRPayment->setPartner($partner)->setData((object)($data))->generate();
        return http_response($request, null, 200, ["qr_string" => $qr_string]);
    }
}