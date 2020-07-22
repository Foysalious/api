<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use Illuminate\Database\QueryException;
use DB;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class RechargeComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $this->storeTransaction();
                $this->completePayment();
                $payable      = $this->payment->payable;
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

    private function storeTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;
        (new WalletTransactionHandler())->setModel($user)->setAmount((double)$this->payment->payable->amount)->setType('credit')->setLog('Credit Purchase')->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setSource($this->payment->paymentDetails->last()->method)->store();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
