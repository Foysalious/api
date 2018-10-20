<?php

namespace Sheba\Payment\Complete;

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
                    'amount' => $this->payment->payable->amount, 'transaction_details' => $this->payment->transaction_details,
                    'type' => 'Credit', 'log' => 'Credit Purchase'
                ]);
                $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status,
                    'transaction_details' => $this->payment->transaction_details]);
                $this->payment->status = 'completed';
                $this->payment->update();
            });
        } catch (QueryException $e) {
            $this->paymentRepository->changeStatus(['to' => 'failed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'failed';
            $this->payment->update();
            throw $e;
        }
        return $this->payment;
    }
}