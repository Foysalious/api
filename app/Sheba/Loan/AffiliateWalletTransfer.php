<?php namespace Sheba\Loan;

use App\Models\Affiliate;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class AffiliateWalletTransfer
{
    private $affiliate, $amount, $loan_id;

    public function setAffiliate(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setLoanId($loan_id)
    {
        $this->loan_id = $loan_id;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function process()
    {
        (new WalletTransactionHandler())
            ->setModel($this->affiliate)
            ->setType(Types::credit())
            ->setSource(TransactionSources::LOAN)
            ->setAmount($this->amount)
            ->setLog($this->getLog())
            ->dispatch();

        $this->sendNotificationToBankPortal();

    }

    private function getLog()
    {
        return "Sheba facilitated amount $this->amount has been received.";
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function sendNotificationToBankPortal()
    {
        $title = "Sheba facilitated amount transferred to sManager";
        Notifications::toBankUser(1, $title, null, $this->loan_id);
    }
}