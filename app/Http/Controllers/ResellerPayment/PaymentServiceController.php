<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\DigitalCollectionSetting\Model as DigitalCollectionSetting;
use Sheba\Dal\PgwStore\Model as PgwStore;
use Sheba\PaymentLink\PaymentLinkStatics;

class PaymentServiceController extends Controller
{
    public function getPaymentGateway(Request $request, PgwStore $pgwStore)
    {
        try {
            $pgwData = [];
            $pgwStores = $pgwStore->select('id', 'name', 'key', 'name_bn', 'icon')->get();

            foreach ($pgwStores as $pgwStore) {
                $pgwData[] = [
                    'id' => $pgwStore->id,
                    'name' => $pgwStore->name,
                    'key' => $pgwStore->key,
                    'name_bn' => $pgwStore->name_bn,
                    'icon' => $pgwStore->icon,
                ];
            }
            return api_response($request, null, 200, ['data' => $pgwData]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentServiceCharge(Request $request)
    {
        try {
            $partnerId = $request->partner->id;
            $digitalCollection = DigitalCollectionSetting::where('partner_id', $partnerId)->select('service_charge')->first();

            $data = PaymentLinkStatics::customPaymentServiceData();
            $data['current_percentage'] = $digitalCollection->service_charge;

            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }


    public function storePaymentServiceCharge(Request $request)
    {
        try {
            $this->validate($request, [
                "current_percentage" => "required"
            ]);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}