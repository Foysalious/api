<?php

namespace Sheba\PayCharge\Complete;


use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use Illuminate\Database\QueryException;
use Sheba\PayCharge\PayChargable;
use DB;
class AdvancedOrderComplete extends PayChargeComplete
{

    public function complete(PayChargable $pay_chargable, $method_response)
    {
        $partner_order_payment = new PartnerOrderPayment();
        try {
            DB::transaction(function () use ($pay_chargable, $method_response, $partner_order_payment) {
                $partner_order = PartnerOrder::find((int)$pay_chargable->id);
                $partner_order->sheba_collection = $pay_chargable->amount;
                $partner_order->update();
                $partner_order_payment->partner_order_id = $partner_order->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->amount = (double)$partner_order->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $pay_chargable->user_id;
                $partner_order_payment->created_by_type = $pay_chargable->user_type;
                $partner_order_payment->created_by_name = 'Customer - ' . $partner_order->order->customer->profile->name;
                $partner_order_payment->transaction_detail = json_encode($method_response);
                $partner_order_payment->method = 'bkash';
//                $partner_order_payment->fill((new UserRequestInformation($request))->getInformationArray());
                $partner_order_payment->save();
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
        return $partner_order_payment;
    }
}