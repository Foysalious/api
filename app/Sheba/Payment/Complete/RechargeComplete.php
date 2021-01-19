<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use Illuminate\Database\QueryException;
use DB;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Dal\PaymentGateway\Contract as PaymentGatewayRepo;

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
                $this->storeCommissionTransaction();
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
        (new WalletTransactionHandler())->setModel($user)->setAmount((double)$this->payment->payable->amount)->setType(Types::credit())->setLog('Credit Purchase')->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setSource($this->payment->paymentDetails->last()->method)->store();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    private function storeCommissionTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;
        dump($this->payment->paymentDetails->last()->method);
        dd($user = $this->payment->created_by_type);
        $payment_gateways = app(PaymentGatewayRepo::class);
        $payment_gateway = $payment_gateways->builder()
            ->where('service_type', "App\\Models\\" . ucwords($user))
            ->where('name', $this->payment->paymentDetails->last()->method)
            ->where('status', 'Published')
            ->get()
            ->first();

        if($payment_gateway){
            (new WalletTransactionHandler())->setModel($user)
                ->setAmount((double)( ($payment_gateway->cash_in_charge * $this->payment->payable->amount) / 100))
                ->setType(Types::debit())
                ->setLog('Credit Purchase Commission')
                ->setTransactionDetails($this->payment->getShebaTransaction()->toArray())
                ->setSource($this->payment->paymentDetails->last()->method)
                ->store();
        }
    }
}
