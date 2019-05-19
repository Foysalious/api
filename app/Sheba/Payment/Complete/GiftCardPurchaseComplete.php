<?php

namespace Sheba\Payment\Complete;

use App\Models\GiftCard;
use App\Models\GiftCardPurchase;
use Illuminate\Database\QueryException;
use DB;

class GiftCardPurchaseComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $gift_card_purchase = GiftCardPurchase::find($this->payment->payable->type_id);
                $gift_card_purchase->status = 'successful';
                $gift_card_purchase->update();
                $this->payment->payable->user->rechargeWallet($gift_card_purchase->credits_purchased, [
                    'amount' => $gift_card_purchase->credits_purchased,
                    'transaction_details' => $this->payment->transaction_details,
                    'type' => 'Credit',
                    'log' => 'Credit Purchase'
                ]);
                $this->completePayment();
            });
        } catch (QueryException $e) {
            $gift_card_purchase = GiftCardPurchase::find($this->payment->payable->type_id);
            $gift_card_purchase->status = 'failed';
            $gift_card_purchase->update();
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }
}