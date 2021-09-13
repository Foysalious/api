<?php


namespace Sheba\PaymentLink;

use App\Models\Payment;
use App\Models\PosCustomer;
use App\Sheba\AccountingEntry\Repository\PaymentLinkAccountingRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class PaymentLinkTransaction
{
    private $amount             = 0;
    private $paidBy;
    private $interest           = 0;
    private $bankTransactionFee = 0;
    private $partnerProfit;
    private $isOld;
    private $formattedRechargeAmount;
    /** @var HasWalletTransaction $receiver */
    private $receiver;
    private $customer;
    private $tax;
    private $walletTransactionHandler;
    /**
     * @var PaymentLinkTransformer
     */
    private $paymentLink;
    /**
     * @var Payment
     */
    private $payment;
    private $rechargeTransaction;
    private $linkCommission;
    /**
     * @var string[]
     */
    private $paidByTypes;
    private $fee = 0;
    /**
     * @var int
     */
    private $entryAmount = 0;

    /*** @var SubscriptionWisePaymentLinkCharges */
    private $paymentLinkCharge;

    /**
     * @param Payment                $payment
     * @param PaymentLinkTransformer $linkTransformer
     */

    public function __construct(Payment $payment, PaymentLinkTransformer $linkTransformer)
    {
        $this->tax                      = PaymentLinkStatics::get_payment_link_tax();
        $this->walletTransactionHandler = (new WalletTransactionHandler());
        $this->paymentLink              = $linkTransformer;
        $this->payment                  = $payment;
        $this->linkCommission           = PaymentLinkStatics::get_payment_link_commission();
        $this->paidByTypes              = PaymentLinkStatics::paidByTypes();
        $this->partnerProfit            = $this->paymentLink->getPartnerProfit();
        $this->interest                 = $this->paymentLink->getInterest();
        $this->paymentLinkCharge        = (new SubscriptionWisePaymentLinkCharges());
    }

    public function isOld()
    {
        return $this->paymentLink->isOld();
    }

    /**
     * @return double
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * @return int
     */
    public function getEmiMonth()
    {
        return $this->paymentLink->getEmiMonth();
    }

    /**
     * @return double
     */
    public function getBankTransactionFee()
    {
        return $this->bankTransactionFee;
    }

    /**
     * @return double
     */
    public function getPartnerProfit()
    {
        return $this->partnerProfit;
    }

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->paymentLink->getAmount();
    }


    /**
     * @param mixed $receiver
     * @return PaymentLinkTransaction
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function getFee()
    {
        return $this->fee;
    }

    public function getEntryAmount()
    {
        return $this->entryAmount;
    }

    public function create()
    {
        $this->walletTransactionHandler->setModel($this->receiver);
        $paymentLinkTransaction = $this->amountTransaction()->interestTransaction()->configurePaymentLinkCharge()->feeTransaction()->setEntryAmount();
        $this->storePaymentLinkEntry($this->amount, $this->fee, $this->interest);
        return $paymentLinkTransaction;

    }

    private function amountTransaction()
    {
        $this->amount                  = $this->payment->payable->amount;
        $this->formattedRechargeAmount = number_format($this->amount, 2);
        $recharge_log                  = "$this->formattedRechargeAmount TK has been collected from {$this->payment->payable->getName()}, {$this->paymentLink->getReason()}";
        $this->rechargeTransaction     = $this->walletTransactionHandler->setType(Types::credit())->setAmount($this->amount)->setSource(TransactionSources::PAYMENT_LINK)->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setLog($recharge_log)->store();
        return $this;
    }

    private function interestTransaction()
    {
        if ($this->paymentLink->isEmi()) {
            $formatted_interest = number_format($this->interest, 2);
            $log                = "$formatted_interest TK has been charged as emi interest fees against of Transc ID {$this->rechargeTransaction->id}, and Transc amount $this->formattedRechargeAmount";
            $this->walletTransactionHandler->setLog($log)->setType(Types::debit())->setAmount($this->interest)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        }
        return $this;
    }

    private function configurePaymentLinkCharge(): PaymentLinkTransaction
    {
        if($this->paymentLinkCharge->isPartner($this->receiver) && !$this->paymentLink->isEmi()) {
            $this->paymentLinkCharge->setPartner($this->receiver)->setPaymentConfigurations($this->getPaymentMethod());
            $this->tax = $this->paymentLinkCharge->getFixedTaxAmount();
            $this->linkCommission = $this->paymentLinkCharge->getGatewayChargePercentage();
        }
        return $this;
    }

    private function getPaymentMethod()
    {
        $payment_details = $this->payment->paymentDetails()->orderBy('id', 'DESC')->first();
        return isset($payment_details) ? $payment_details->method : null;
    }

    public function isPaidByPartner()
    {
        return $this->paymentLink->getPaidBy() == $this->paidByTypes[0];
    }

    public function isPaidByCustomer()
    {
        return $this->paymentLink->getPaidBy() == $this->paidByTypes[1];
    }

    private function feeTransaction()
    {
        if ($this->paymentLink->isEmi()) {
            $this->fee = $this->paymentLink->isOld() || $this->isPaidByPartner() ? $this->paymentLink->getBankTransactionCharge() + $this->tax : $this->paymentLink->getBankTransactionCharge() - $this->paymentLink->getPartnerProfit();
        } else {
            $realAmount = $this->paymentLink->getRealAmount() !== null ? $this->paymentLink->getRealAmount() : $this->calculateRealAmount();
            $this->fee  = $this->paymentLink->isOld() || $this->isPaidByPartner() ? round(($this->amount * $this->linkCommission / 100) + $this->tax, 2) : round(($realAmount * $this->linkCommission / 100) + $this->tax, 2);

        }
        $formatted_minus_amount = number_format($this->fee, 2);
        $minus_log              = "($this->tax" . "TK + $this->linkCommission%) $formatted_minus_amount TK has been charged as link service fees against of Transc ID: {$this->rechargeTransaction->id}, and Transc amount: $this->formattedRechargeAmount";
        $this->walletTransactionHandler->setLog($minus_log)->setType(Types::debit())->setAmount($this->fee)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        return $this;
    }


    private function setEntryAmount()
    {
        $amount = $this->getAmount();
        if ($this->isPaidByPartner()) {
            $this->entryAmount = $amount;
        } else {
            if ($this->paymentLink->isEmi()) {
                $this->entryAmount = $amount - $this->getFee() - $this->getInterest();
            } else {
                $this->entryAmount = $amount - $this->getFee() - $this->partnerProfit;
            }
        }
        return $this;
    }

    /**
     * @return float
     */
    private function calculateRealAmount(): float
    {
        $amount_after_tax_profit = $this->amount - $this->getPartnerProfit() - 3;
        $real_amount = ( 100 * $amount_after_tax_profit) / 102;
        return round($real_amount, 2);
    }

    /**
     * @param $amount
     * @param $feeTransaction
     * @param $interest
     */
    private function storePaymentLinkEntry($amount, $feeTransaction, $interest) {
        $customer = null;
        if (isset($this->customer)) {
            $customer = PosCustomer::where('profile_id', $this->customer->profile->id)->first();
        }
        /** @var PaymentLinkAccountingRepository $paymentLinkRepo */
        $paymentLinkRepo =  app(PaymentLinkAccountingRepository::class);
        $transaction = $paymentLinkRepo->setAmount($amount)
            ->setBankTransactionCharge($feeTransaction)
            ->setInterest($interest)
            ->setAmountCleared($amount);
        if ($customer) {
            $transaction = $transaction->setCustomerId(isset($customer) ? $customer->id: null)
                    ->setCustomerName(isset($this->customer) ? $this->customer->profile->name: null);
        }
        $transaction->store($this->receiver->id);
    }
}