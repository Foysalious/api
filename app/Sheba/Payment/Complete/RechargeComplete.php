<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use DB;
use Illuminate\Database\QueryException;
use ReflectionException;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\PaymentGateway\Contract as PaymentGatewayRepo;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class RechargeComplete extends PaymentComplete
{
    private $transaction;
    private $paymentGateway;

    public function complete()
    {
        try {
            $this->payment->reload();
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $this->storeTransaction();
                $this->completePayment();
                $payable      = $this->payment->payable;
                $payable_user = $payable->user;
                $this->setPaymentGateWay();
                if ($payable_user instanceof Partner) {
                    app(ActionRewardDispatcher::class)->run('partner_wallet_recharge', $payable_user, $payable_user, $payable);
                    $this->storeJournal();
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
        $user              = $this->payment->payable->user;
        $this->transaction = (new WalletTransactionHandler())->setModel($user)->setAmount((double)$this->payment->payable->amount)->setType(Types::credit())->setLog('Credit Purchase')->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setSource($this->payment->paymentDetails->last()->method)->store();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    /**
     * @param $charge
     * @return float
     */
    private function calculateCommission($charge): float
    {
        if ($this->payment->payable->user instanceof Partner) return round(($this->payment->payable->amount / (100 + $charge)) * $charge, 2);
        return (double)round(($charge * $this->payment->payable->amount) / 100, 2);
    }

    private function storeCommissionTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;

        if ($this->paymentGateway && $this->paymentGateway->cash_in_charge > 0) {
            $amount = $this->calculateCommission($this->paymentGateway->cash_in_charge);
            (new WalletTransactionHandler())->setModel($user)
                ->setAmount($amount)
                ->setType(Types::debit())
                ->setLog($amount . ' BDT has been deducted as a gateway charge for SHEBA credit recharge')
                ->setTransactionDetails($this->payment->getShebaTransaction()->toArray())
                ->setSource($this->payment->paymentDetails->last()->method)
                ->store();
        }
    }

    private function setPaymentGateWay()
    {
        $payment_gateways = app(PaymentGatewayRepo::class);
        $this->paymentGateway = $payment_gateways->builder()
            ->where('service_type', $this->payment->created_by_type)
            ->where('method_name', $this->payment->paymentDetails->last()->method)
            ->where('status', 'Published')
            ->first();
    }

    /**
     * @throws ReflectionException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException|KeyNotFoundException
     */
    private function storeJournal()
    {
        $payable = $this->payment->payable;
        $commission = isset($this->paymentGateway) ? $this->calculateCommission($this->paymentGateway->cash_in_charge) : 0;
        (new JournalCreateRepository())->setTypeId($payable->user->id)
            ->setSource($this->transaction)->setAmount($payable->amount)
            ->setDebitAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setCreditAccountKey($this->payment->paymentDetails->last()->method)
            ->setDetails("Entry For Wallet Transaction")
            ->setCommission($commission)->setEndPoint("api/journals/wallet")
            ->setReference($this->payment->id)->store();
    }
}
