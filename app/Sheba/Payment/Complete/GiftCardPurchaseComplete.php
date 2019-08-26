<?php

namespace Sheba\Payment\Complete;

use Illuminate\Database\QueryException;
use App\Models\GiftCardPurchase;
use Sheba\ModificationFields;
use App\Models\Bonus;
use Carbon\Carbon;
use DB;

class GiftCardPurchaseComplete extends PaymentComplete
{
    use ModificationFields;

    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $gift_card_purchase = GiftCardPurchase::find($this->payment->payable->type_id);
                $gift_card = $gift_card_purchase->giftCard;
                $gift_card_purchase->status = 'successful';
                $gift_card_purchase->update();
                Bonus::create([
                    'user_type' => get_class($this->payment->payable->user),
                    'user_id' => $this->payment->payable->user->id,
                    'type' => 'cash',
                    'amount' => (double)$gift_card->credit,
                    'log' => "$gift_card->credit tk gift card purchase",
                    'status' => 'valid',
                    'valid_till' => Carbon::now()->addMonth(6),
                    'created_by_name' => $this->payment->payable->getName()
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

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}