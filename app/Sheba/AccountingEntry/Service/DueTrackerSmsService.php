<?php namespace App\Sheba\AccountingEntry\Service;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Transactions\Types;
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
    public function getSmsContentForTagada()
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
            'partner_name' => $partner_info['name'],
            'partner_mobile' => $partner_info['mobile'],
            'web_report_link' => 'www.google.com',
        ];
    }

    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function sendSingleSmsToContact()
    {
        $contact_balance = $this->getSmsContentForTagada();
        dd($contact_balance);
    }

    public function sendSMS()
    {
        $smsType = [
            'receivable' => 'due',
            'payable' => 'deposit',
        ];
        $type = $smsType[''];

        /** @var Partner $partner */
        $partner = $this->partner;
        $data = [
            'type' => $type,
            'partner_name' => $partner->name,
            'customer_name' => $customer->name,
            'mobile' => $customer->mobile,
            'amount' => $request->amount,
            'company_number' => $partner->getContactNumber()
        ];

        /** @var SmsHandlerRepo $sms */
        list($sms, $log) = $this->getSms($data);
        $sms_cost = $sms->estimateCharge();
        /*
        if ((double)$request->partner->wallet < $sms_cost) throw new InsufficientBalance();
        //freeze money amount check
        WalletTransactionHandler::isDebitTransactionAllowed($request->partner, $sms_cost, 'এস-এম-এস পাঠানোর');
        $sms->setBusinessType(BusinessType::SMANAGER)->setFeatureType(FeatureType::DUE_TRACKER);
        if (config('sms.is_on')) $sms->shoot();
        $transaction = (new WalletTransactionHandler())
            ->setModel($this->partner)
            ->setAmount($sms_cost)
            ->setType(Types::debit())
            ->setLog($sms_cost . $log)
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();
        $this->storeJournal($request->partner, $transaction);
        */
    }

    public function getSms($data)
    {
        $log = " BDT has been deducted for sending ";
        $message_data = [
            'customer_name'  => $data['customer_name'],
            'partner_name'   => $data['partner_name'],
            'amount'         => $data['amount'],
            'company_number' => $data['company_number']
        ];

        if ($data['type'] == 'due') {
            $sms                          = (new SmsHandlerRepo('inform-due'));
            $log                          .= "due details";
        } else {
            $sms = (new SmsHandlerRepo('inform-deposit'));
            $log .= "deposit details";
        }

        $sms = $sms
            ->setMobile($data['mobile'])
            ->setMessage($message_data)
            ->setFeatureType(FeatureType::DUE_TRACKER)
            ->setBusinessType(BusinessType::SMANAGER);
        return [$sms, $log];
    }
}