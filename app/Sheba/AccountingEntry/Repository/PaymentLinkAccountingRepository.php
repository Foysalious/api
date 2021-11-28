<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class PaymentLinkAccountingRepository extends AccountingRepository
{
    private $amount;
    private $bank_transaction_charge;
    private $interest;
    private $source_type = EntryTypes::PAYMENT_LINK;
    private $debit_account_key;
    private $credit_account_key;
    private $amount_cleared;
    private $source_id;
    private $customer_id;
    private $customer_name;
    private $customer_mobile;
    private $customer_pro_pic;
    private $customer_is_supplier;
    private $note;
    private $details;


    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
    }

    /**
     * @param mixed $amount
     * @return PaymentLinkAccountingRepository
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param AccountingEntryClient $client
     * @return PaymentLinkAccountingRepository
     */
    public function setClient(AccountingEntryClient $client): PaymentLinkAccountingRepository
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return PaymentLinkAccountingRepository
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $customer_name
     * @return PaymentLinkAccountingRepository
     */
    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
        return $this;
    }

    /**
     * @param mixed $customer_mobile
     * @return PaymentLinkAccountingRepository
     */
    public function setCustomerMobile($customer_mobile)
    {
        $this->customer_mobile = $customer_mobile;
        return $this;
    }

    /**
     * @param mixed $customer_pro_pic
     * @return PaymentLinkAccountingRepository
     */
    public function setCustomerProPic($customer_pro_pic)
    {
        $this->customer_pro_pic = $customer_pro_pic;
        return $this;
    }

    /**
     * @param mixed $customer_is_supplier
     * @return PaymentLinkAccountingRepository
     */
    public function setCustomerIsSupplier($customer_is_supplier)
    {
        $this->customer_is_supplier = $customer_is_supplier;
        return $this;
    }

    /**
     * @param mixed $note
     * @return PaymentLinkAccountingRepository
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $details
     * @return PaymentLinkAccountingRepository
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $source_type
     * @return PaymentLinkAccountingRepository
     */
    public function setSourceType(string $source_type): PaymentLinkAccountingRepository
    {
        $this->source_type = $source_type;
        return $this;
    }

    /**
     * @param mixed $source_id
     * @return PaymentLinkAccountingRepository
     */
    public function setSourceId($source_id)
    {
        $this->source_id = $source_id;
        return $this;
    }


    /**
     * @param mixed $amount_cleared
     * @return PaymentLinkAccountingRepository
     */
    public function setAmountCleared($amount_cleared)
    {
        $this->amount_cleared = $amount_cleared;
        return $this;
    }


    /**
     * @param mixed $bank_transaction_charge
     * @return PaymentLinkAccountingRepository
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return PaymentLinkAccountingRepository
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $debit_account_key
     * @return PaymentLinkAccountingRepository
     */
    public function setDebitAccountKey($debit_account_key)
    {
        $this->debit_account_key = $debit_account_key;
        return $this;
    }

    /**
     * @param mixed $credit_account_key
     * @return PaymentLinkAccountingRepository
     */
    public function setCreditAccountKey($credit_account_key)
    {
        $this->credit_account_key = $credit_account_key;
        return $this;
    }

    /**
     * @param $userId
     * @return bool|mixed
     * @throws AccountingEntryServerError
     */
    public function store($userId)
    {
        $payload = $this->makeData($userId);
        return $this->storeEntry((object)$payload, EntryTypes::PAYMENT_LINK);
    }

    /**
     * @param $userId
     * @return bool|mixed
     * @throws AccountingEntryServerError
     */
    public function updatePaymentLinkEntry($userId)
    {
        $payload = $this->makeData($userId);
        return $this->updateEntryBySource((object)$payload, $this->source_id, $this->source_type);
    }

    /**
     * @param $userId
     * @return mixed
     */
    private function makeData($userId)
    {
        if ($this->debit_account_key == null && $this->credit_account_key == null) {
            $this->setDebitAccountKey((new Accounts())->expense->paymentLinkServiceCharge::PAYMENT_LINK_SERVICE_CHARGE);

            if ($this->interest > 0) {
                $this->setCreditAccountKey((new Accounts())->income->incomeFromEmi::INCOME_FROM_EMI);
            } else {
                $this->setCreditAccountKey((new Accounts())->income->incomeFromPaymentLink::INCOME_FROM_PAYMENT_LINK);
            }
        }

        $data['customer_id'] = $this->customer_id;
        $data['customer_name'] = $this->customer_name;
        $data['customer_mobile'] = $this->customer_mobile;
        $data['customer_pro_pic'] = $this->customer_pro_pic;
        $data['customer_is_supplier'] = $this->customer_is_supplier;
        $data['amount'] = $this->amount;
        $data['amount_cleared'] = $this->amount_cleared;
        $data['entry_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $data['bank_transaction_charge'] = $this->bank_transaction_charge;
        $data['interest'] = $this->interest;
        $data['source_id'] = $this->source_id;
        $data['source_type'] = $this->source_type;
        $data['to_account_key'] = $this->debit_account_key;
        $data['from_account_key'] = $this->credit_account_key;
        $data['reference'] = 'Entry using Payment Link';
        $data['note'] = $this->note;
        $data['details'] = $this->details;
        $data['partner'] = $userId;
        return $data;
    }
}