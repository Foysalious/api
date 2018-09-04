<?php

namespace Sheba\PayCharge\Complete;


use App\Models\PartnerOrderPayment;
use Sheba\PayCharge\PayChargable;

class AdvancedOrderComplete extends PayChargeComplete
{

    public function complete(PayChargable $pay_chargable, $method_response)
    {
        $partner_order_payment = new PartnerOrderPayment();
        try {
            DB::transaction(function () use ($pay_chargable, $method_response) {
                $partnerOrder->sheba_collection = $amount;
                $partnerOrder->update();
                $partner_order_payment->partner_order_id = $partnerOrder->id;
                $partner_order_payment->transaction_type = 'Debit';
                $partner_order_payment->amount = (double)$partnerOrder->sheba_collection;
                $partner_order_payment->log = 'advanced payment';
                $partner_order_payment->collected_by = 'Sheba';
                $partner_order_payment->created_by = $partnerOrder->order->customer_id;
                $partner_order_payment->created_by_type = "App\Models\Customer";
                $partner_order_payment->created_by_name = 'Customer - ' . $partnerOrder->order->customer->profile->name;
                $partner_order_payment->transaction_detail = $this->paymentGateway->formatTransactionData($gateway_response);
                $partner_order_payment->method = 'bkash';
                $partner_order_payment->fill((new UserRequestInformation($request))->getInformationArray());
                $partner_order_payment->save();
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
        return $partner_order_payment;
    }
}