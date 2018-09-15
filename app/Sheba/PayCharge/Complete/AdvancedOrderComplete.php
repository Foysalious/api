<?php

namespace Sheba\PayCharge\Complete;


use App\Models\Customer;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use Sheba\PayCharge\PayChargable;
use DB;
use Sheba\RequestIdentification;

class AdvancedOrderComplete extends PayChargeComplete
{
    use ModificationFields;
    CONST CASHBACK_PERCENTAGE = 1;

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
                $partner_order_payment->transaction_detail = json_encode($method_response['details']);
                $partner_order_payment->method = $method_response['name'];
                /** @var Customer $customer */
                $customer = Customer::find($pay_chargable->userId);
                $this->setModifier($customer);
                $this->withCreateModificationField($partner_order_payment);
                $partner_order_payment->fill((new RequestIdentification())->get());
                $partner_order_payment->save();
                if (strtolower($method_response['name']) == 'wallet') {
                    $amount = ($pay_chargable->amount * self::CASHBACK_PERCENTAGE) / 100;
                    $customer->rechargeWallet($amount, [
                        'amount' => $amount, 'transaction_details' => json_encode($method_response['details']),
                        'type' => 'Credit', 'log' => 'Bonus Sheba Credit'
                    ]);
                }
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $partner_order_payment;
    }
}