<?php


namespace Sheba\PaymentLink;


use App\Models\Payment;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class PaymentLinkTransaction
{
    private $amount;
    private $paidBy;
    private $interest;
    private $emiMonth;
    private $bankTransactionFee;
    private $partnerProfit;
    private $isOld;
    private $formattedRechargeAmount;
    /** @var HasWalletTransaction $receiver */
    private $receiver;
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
    private $fee;

    public function __construct(Payment $payment, PaymentLinkTransformer $linkTransformer)
    {
        $this->tax                      = PaymentLinkStatics::get_payment_link_tax();
        $this->walletTransactionHandler = (new WalletTransactionHandler());
        $this->paymentLink              = $linkTransformer;
        $this->payment                  = $payment;
        $this->linkCommission           = PaymentLinkStatics::get_payment_link_commission();
        $this->paidByTypes              = PaymentLinkStatics::paidByTypes();
    }

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

    public function create()
    {
        $this->walletTransactionHandler->setModel($this->receiver);
        $this->amountTransaction()->interestTransaction()->feeTransaction();
        return $this;
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
            $interest           = $this->paymentLink->getInterest();
            $formatted_interest = number_format($interest, 2);
            $log                = "$formatted_interest TK has been charged as emi interest fees against of Transc ID {$this->rechargeTransaction->id}, and Transc amount $this->formattedRechargeAmount";
            $this->walletTransactionHandler->setLog($log)->setType(Types::debit())->setAmount($interest)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        }
        return $this;
    }

    private function isPaidByPartner()
    {
        return $this->paymentLink->getPaidBy() == $this->paidByTypes[0];
    }

    private function isPaidByCustomer()
    {
        return $this->paymentLink->getPaidBy() == $this->paidByTypes[0];
    }

    private function feeTransaction()
    {
        if ($this->paymentLink->isEmi()) {
            $this->fee = $this->paymentLink->isOld() || $this->isPaidByPartner() ? $this->paymentLink->getBankTransactionCharge() + $this->tax : $this->paymentLink->getBankTransactionCharge();
        } else {
            $realAmount = ($this->amount - $this->tax - $this->paymentLink->getPartnerProfit());
            $this->fee  = $this->paymentLink->isOld() || $this->isPaidByPartner() ? round(($this->amount * $this->linkCommission / 100) + $this->tax, 2) : round(($realAmount / (100 + $this->linkCommission)) * 100, 2);
        }

        $formatted_minus_amount = number_format($this->fee, 2);
        $minus_log              = "($this->tax" . "TK + $this->linkCommission%) $formatted_minus_amount TK has been charged as link service fees against of Transc ID: {$this->rechargeTransaction->id}, and Transc amount: $this->formattedRechargeAmount";
        $this->walletTransactionHandler->setLog($minus_log)->setType(Types::debit())->setAmount($this->fee)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
    }

    public function getFee()
    {
        return $this->fee;
    }


}
