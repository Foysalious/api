<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Reward\ActionRewardDispatcher;

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
                $payable_user = $this->payment->payable->user;
                $payable_amount = $this->payment->payable->amount;
                if ($payable_user instanceof Partner) {
                    app(ActionRewardDispatcher::class)->run('partner_wallet_recharge', $payable_user, $payable_amount, $payable_user);
                }
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