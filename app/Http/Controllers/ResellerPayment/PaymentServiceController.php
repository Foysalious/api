<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\DigitalCollectionSetting\Model as DigitalCollectionSetting;
use Sheba\Dal\PgwStore\Model as PgwStore;

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
//            $digitalCollection = DigitalCollectionSetting::where('partner_id', $partnerId)->select('service_charge')->first();

            $data = [
                "step"                           => self::get_step_margin(),
                "minimum_percentage"             => self::get_minimum_percentage(),
                "maximum_percentage"             => self::get_maximum_percentage(),
                "current_percentage"             => 2
            ];

            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public static function get_step_margin()
    {
        return config('payment_link.step_margin');
    }

    public static function get_minimum_percentage()
    {
        return config('payment_link.minimum_percentage');
    }

    public static function get_maximum_percentage()
    {
        return config('payment_link.maximum_percentage');
    }
}