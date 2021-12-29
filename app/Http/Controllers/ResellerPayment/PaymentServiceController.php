<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\PgwStore\Model as PgwStore;

class PaymentServiceController extends Controller
{
    public function getPaymentGateway(Request $request, PgwStore $pgwStore)
    {
        try {
            $pgwData = [];
            $pgwStores = $pgwStore->select('id', 'name', 'key', 'name_bn', 'icon')->get();

            foreach ($pgwStores as $pgwStore) {
                $pgwData[$pgwStore->key] = [
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
}