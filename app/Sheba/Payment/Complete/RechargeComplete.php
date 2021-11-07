<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Sheba\Dal\PaymentGateway\Contract as PaymentGatewayRepo;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class RechargeComplete extends PaymentComplete
{
    /**
     * @throws WalletDebitForbiddenException
     */
    public function complete()
    {
        try {
            $this->payment->reload();
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $transaction=$this->storeTransaction();
                if ($transaction){
                    $this->completePayment();
                    $this->storeCommissionTransaction();
                }
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        } catch (WalletDebitForbiddenException $e) {
            $this->failPayment();
            throw $e;
        }
        $payable      = $this->payment->payable;
        $payable_user = $payable->user;
        if ($payable_user instanceof Partner) {
            app(ActionRewardDispatcher::class)->run('partner_wallet_recharge', $payable_user, $payable_user, $payable);
        }
        return $this->payment;
    }

    /**
     * @throws WalletDebitForbiddenException
     */
    private function storeTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;
        return (new WalletTransactionHandler())->setModel($user)->setAmount((double)$this->payment->payable->amount)->setType(Types::credit())->setLog('Credit Purchase')->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setSource($this->payment->paymentDetails->last()->method)->store();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    private function calculateCommission($charge)
    {
        if ($this->payment->payable->user instanceof Partner) return round(($this->payment->payable->amount / (100 + $charge)) * $charge, 2);
        return (double)round(($charge * $this->payment->payable->amount) / 100, 2);
    }

    private function storeCommissionTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;

        $payment_gateways = app(PaymentGatewayRepo::class);
        $payment_gateway  = $payment_gateways->builder()
            ->where('service_type', $this->payment->created_by_type)
            ->where('method_name', $this->payment->paymentDetails->last()->method)
            ->where('status', 'Published')
            ->first();

        if ($payment_gateway && $payment_gateway->cash_in_charge > 0) {
            $amount = $this->calculateCommission($payment_gateway->cash_in_charge);
            (new WalletTransactionHandler())->setModel($user)
                ->setAmount($amount)
                ->setType(Types::debit())
                ->setLog($amount . ' BDT has been deducted as a gateway charge for SHEBA credit recharge')
                ->setTransactionDetails($this->payment->getShebaTransaction()->toArray())
                ->setSource($this->payment->paymentDetails->last()->method)
                ->store();
        }
    }
}
