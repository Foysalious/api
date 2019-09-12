<?php namespace Sheba\Payment\Complete;

use Illuminate\Database\QueryException;
use DB;

class RechargeComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $this->payment->payable->user->rechargeWallet($this->payment->payable->amount, [
                    'amount' => $this->payment->payable->amount,
                    'transaction_details' => $this->payment->getShebaTransaction()->toJson(),
                    'type' => 'Credit',
                    'log' => 'Credit Purchase'
                ]);
                $this->completePayment();
            });
        } catch (QueryException $e) {
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