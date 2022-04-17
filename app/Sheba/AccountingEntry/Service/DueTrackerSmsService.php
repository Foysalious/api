<?php namespace App\Sheba\AccountingEntry\Service;

use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\AccountingEntry\Constants\BalanceType;
use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Exception;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class DueTrackerSmsService
{
    protected $partner;
    protected $contact_type;
    protected $contact_id;
    protected $dueTrackerRepo;
    protected $dueTrackerService;

    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo, DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
        $this->dueTrackerService = $dueTrackerService;
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
     * @param mixed $contact_type
     */
    public function setContactType($contact_type)
    {
        $this->contact_type = $contact_type;
        return $this;
    }

    /**
     * @param mixed $contact_id
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;
        return $this;
    }


    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function getSmsContentForTagada(): array
    {
        $contact_balance = $this->dueTrackerService
            ->setContactType($this->contact_type)
            ->setContactId($this->contact_id)
            ->setPartner($this->partner)
            ->getBalanceByContact();
        $partner_info = $this->dueTrackerService->getPartnerInfo($this->partner);
        return [
            'balance' => $contact_balance['stats']['balance'],
            'balance_type' => $contact_balance['stats']['type'],
            'contact_name' => $contact_balance['contact_details']['name'],
            'contact_mobile' => $contact_balance['contact_details']['mobile'],
            'partner_name' => $partner_info['name'],
            'partner_mobile' => $partner_info['mobile'],
            'web_report_link' => 'www.google.com',
        ];
    }

    /**
     * @throws AccountingEntryServerError
     * @throws InsufficientBalance
     * @throws InvalidPartnerPosCustomer
     * @throws WalletDebitForbiddenException
     */
    public function sendSingleSmsToContact()
    {
        $sms_content = $this->getSmsContentForTagada();
        $this->sendSMS($sms_content);
        return true;
    }

    /**
     * @throws WalletDebitForbiddenException
     * @throws InsufficientBalance
     * @throws Exception
     */
    private function sendSMS($sms_content)
    {
        $data = $this->generateSmsDataForContactType($sms_content);
        /** @var SmsHandlerRepo $sms */
        list($sms, $log) = $this->getSmsHandler($data);
        $sms_cost = $sms->estimateCharge();
        if ((double)$this->partner->wallet < $sms_cost) throw new InsufficientBalance();
        WalletTransactionHandler::isDebitTransactionAllowed($this->partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        $sms->setBusinessType(BusinessType::SMANAGER)->setFeatureType(FeatureType::DUE_TRACKER);
        if (config('sms.is_on')) {
            $sms->shoot();
        }
        $this->deductSmsCostFromWallet($sms_cost,$log);
    }

    /**
     * @throws Exception
     */
    public function getSmsHandler($data)
    {
        $log = ' BDT has been deducted for sending ';
        $event_name = 'due-tracker-inform-' . $this->contact_type;
        if ($data['type'] == 'due') {
            $event_name .=   '-due';
            $log .= "due details";
        } else {
            $event_name .= '-deposit';
            $log .= "deposit details";
        }
        $sms = (new SmsHandlerRepo($event_name));
        $sms->setMobile($data['mobile'])
            ->setMessage($data)
            ->setFeatureType(FeatureType::DUE_TRACKER)
            ->setBusinessType(BusinessType::SMANAGER);
        return [$sms, $log];
    }

    private function generateSmsDataForContactType(array $sms_content)
    {
        $data =[
            'partner_name' => $sms_content['partner_name'],
            'mobile' => $sms_content['contact_mobile'],
            'amount' => $sms_content['balance'],
            'partner_mobile' => $sms_content['partner_mobile'],
            'web_report_link' => $sms_content['web_report_link'],
            'type' => $sms_content['balance_type'] == BalanceType::RECEIVABLE ? 'due' : 'deposit'
        ];
        if ( $this->contact_type == ContactType::CUSTOMER) {
            $data['customer_name'] =  $sms_content['contact_name'];
        } else {
            $data['supplier_name'] =  $sms_content['contact_name'];
        }
        return $data;
    }

    /**
     * @throws AccountingEntryServerError
     * @throws WalletDebitForbiddenException
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    private function deductSmsCostFromWallet($sms_cost, $log)
    {
        $transaction = (new WalletTransactionHandler())
            ->setModel($this->partner)
            ->setAmount($sms_cost)
            ->setType(Types::debit())
            ->setLog($sms_cost . $log)
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();
        $this->dueTrackerRepo->storeJournalForSmsSending($this->partner, $transaction);
    }
}