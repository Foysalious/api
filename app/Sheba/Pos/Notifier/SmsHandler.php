<?php namespace Sheba\Pos\Notifier;

use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Exception;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Throwable;

class SmsHandler {

    private $data;
    private $partner;

    public function setData($data) {
        $this->data =$data;
        return $this;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @throws WalletDebitForbiddenException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws InsufficientBalance
     * @throws KeyNotFoundException
     * @throws Exception
     */
    public function handle()
    {
        $sms = $this->getSms();
        $sms_cost = $sms->estimateCharge();
        WalletTransactionHandler::isDebitTransactionAllowed($this->partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        if ((double)$this->data['wallet'] > (double)$sms_cost) {
            /** @var WalletTransactionHandler $walletTransactionHandler */
            try {
                $sms->setBusinessType(BusinessType::SMANAGER)
                    ->setFeatureType(FeatureType::POS)
                    ->shoot();
            } catch(\Throwable $e) {
            }
            $transaction =  (new WalletTransactionHandler())->setModel($this->data['model'])->setAmount($sms_cost)
                ->setType(Types::debit())
                ->setLog($sms_cost . $this->data['log'])->setTransactionDetails([])
                ->setSource(TransactionSources::SMS)->store();
            $this->storeJournal($this->partner, $transaction);
        }
    }

    /**
     * @throws AccountingEntryServerError
     * @throws KeyNotFoundException
     * @throws InvalidSourceException
     */
    private function storeJournal($partner, $transaction) {
        (new JournalCreateRepository())->setTypeId($partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Pos sms sent charge")
            ->setReference("")
            ->store();
    }

    /**
     * @return SmsHandlerRepo
     * @throws Exception
     */
    private function getSms()
    {
        return (new SmsHandlerRepo($this->data['template']))->setMobile($this->data['mobile'])->setFeatureType($this->data['feature_type'])
            ->setBusinessType($this->data['business_type'])->setMessage($this->data['message']);
    }
}
