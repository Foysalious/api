<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class PaymentLinkRepository extends BaseRepository
{
    private $api;
    private $amount;
    private $bank_transaction_charge;
    private $interest;
    private $source_type = EntryTypes::PAYMENT_LINK;
    private $debit_account_key;
    private $credit_account_key;


    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/entries/';
    }

    /**
     * @param mixed $amount
     * @return PaymentLinkRepository
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     * @return PaymentLinkRepository
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return PaymentLinkRepository
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $debit_account_key
     * @return PaymentLinkRepository
     */
    public function setDebitAccountKey($debit_account_key)
    {
        $this->debit_account_key = $debit_account_key;
        return $this;
    }

    /**
     * @param mixed $credit_account_key
     * @return PaymentLinkRepository
     */
    public function setCreditAccountKey($credit_account_key)
    {
        $this->credit_account_key = $credit_account_key;
        return $this;
    }

    public function store($userId, $userType = UserType::PARTNER)
    {
        try {
            $payload = $this->makeData();
            return $this->client->setUserType($userType)->setUserId($userId)->post($this->api, $payload);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function paymentLinkPosOrderJournal($payload, $userId, $userType = UserType::PARTNER)
    {
        $url = "api/entries/source/".$payload['source_type'].'/'.$payload['source_id'];
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->post($url, $payload);
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    private function makeData()
    {
        $this->setDebitAccountKey((new Accounts())->expense->paymentLinkServiceCharge::PAYMENT_LINK_SERVICE_CHARGE);

        if ($this->interest > 0) {
            $this->setCreditAccountKey((new Accounts())->income->incomeFromEmi::INCOME_FROM_EMI);
        } else {
            $this->setCreditAccountKey((new Accounts())->income->incomeFromPaymentLink::INCOME_FROM_PAYMENT_LINK);
        }

        $data['amount'] = $this->amount;
        $data['entry_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $data['bank_transaction_charge'] = $this->bank_transaction_charge;
        $data['interest'] = $this->interest;
        $data['source_type'] = $this->source_type;
        $data['debit_account_key'] = $this->debit_account_key;
        $data['credit_account_key'] = $this->credit_account_key;
        $data['reference'] = 'Entry using Payment Link';

        return $data;
    }


}