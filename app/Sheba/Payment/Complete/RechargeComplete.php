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
                $payable = $this->payment->payable;
                $payable_user = $payable->user;
                if ($payable_user instanceof Partner) {
                    app(ActionRewardDispatcher::class)->run('partner_wallet_recharge', $payable_user, $payable_user, $payable);
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