<?php namespace App\Sheba\AccountingEntry\Service;

use App\Jobs\Partner\DueTrackerBulkSmsSend;
use App\Models\Partner;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\AccountingEntry\Constants\BalanceType;
use App\Sheba\AccountingEntry\Constants\BulkSmsDialogue;
use App\Sheba\AccountingEntry\Constants\ContactType;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use App\Sheba\Partner\PackageFeatureCount;
use Exception;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\ContactDoesNotExistInDueTracker;
use Sheba\AccountingEntry\Exceptions\InsufficientSmsForDueTrackerTagada;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Transactions\Wallet\WalletDebitForbiddenException;

class DueTrackerSmsService
{
    protected $partner;
    protected $contactType;
    protected $contactId;
    protected $dueTrackerRepo;
    protected $dueTrackerService;
    protected $limit;
    protected $offset;
    protected $partnerId;
    protected $contactIds;


    public function __construct(DueTrackerRepositoryV2 $dueTrackerRepo, DueTrackerService $dueTrackerService)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
        $this->dueTrackerService = $dueTrackerService;
    }

    /**
     * @param mixed $partner
     * @return DueTrackerSmsService
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $contact_type
     * @return DueTrackerSmsService
     */
    public function setContactType($contact_type)
    {
        $this->contactType = $contact_type;
        return $this;
    }


    public function setContactIds($contact_ids)
    {
        $this->contactIds = $contact_ids;
        return $this;
    }

    /**
     * @param $contact_id
     * @return DueTrackerSmsService
     */
    public function setContactId($contact_id)
    {
        $this->contactId = $contact_id;
        return $this;
    }

        /**
     * @param mixed $limit
     * @return DueTrackerSmsService
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param mixed $offset
     * @return DueTrackerSmsService
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return DueTrackerSmsService
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError|ContactDoesNotExistInDueTracker
     */
    public function getSmsContentForTagada(): array
    {
        $sms_content = $this->getSmsContentForSingleContact();
        $partner_info = $this->getPartnerInfo();
        return [
            'balance' => $sms_content['balance'],
            'balance_type' => $sms_content['balance_type'],
            'contact_name' => $sms_content['contact_name'],
            'partner_name' => $partner_info['name'],
            'partner_mobile' => $partner_info['mobile'],
            'web_report_link' => $sms_content['web_report_link']
        ];
    }

    /**
     * @return bool
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     * @throws Exception
     */
    public function sendSingleSmsToContact(): bool
    {
        $sms_content = $this->getSmsContentForSingleContact();
        $this->sendSMS($sms_content);
        return true;
    }

    /**
     * @throws Exception
     */
    private function sendSMS($sms_content)
    {
        $data = $this->generateSmsDataForContactType($sms_content);
        $sms = $this->getSmsHandler($data);
        $sms_count = $sms->getSmsCountAndEstimationCharge()['sms_count'];
        $sms_package = app()->make(PackageFeatureCount::class)
            ->setPartnerId($this->partner->id)
            ->setFeature(PackageFeatureCount::SMS);
        if ($sms_package->isEligible($sms_count)) {
            $sms->setBusinessType(BusinessType::SMANAGER)->setFeatureType(FeatureType::DUE_TRACKER);
                $sms->shoot();
                $sms_package->decrementFeatureCount($sms_count);
        } else {
            throw new InsufficientSmsForDueTrackerTagada();
        }
    }

    /**
     * @throws Exception
     */
    public function getSmsHandler($data): SmsHandlerRepo
    {
        $event_name = 'due-tracker-inform-' . $this->contactType;
        if ($data['type'] == 'due') {
            $event_name .=   '-due';
        } else {
            $event_name .= '-deposit';
        }
        $sms = (new SmsHandlerRepo($event_name));
        $sms->setMobile($data['mobile'])
            ->setMessage($data)
            ->setFeatureType(FeatureType::DUE_TRACKER)
            ->setBusinessType(BusinessType::SMANAGER);
        return $sms;
    }

    private function generateSmsDataForContactType(array $sms_content): array
    {
        $partner_info = $this->getPartnerInfo();
        $data =[
            'partner_name' => $partner_info['name'],
            'partner_mobile' => $partner_info['mobile'],
            'mobile' => $sms_content['contact_mobile'],
            'amount' => $sms_content['balance'],
            'web_report_link' => $sms_content['web_report_link'],
            'type' => $sms_content['balance_type'] == BalanceType::RECEIVABLE ? 'due' : 'deposit'
        ];
        if ( $this->contactType == ContactType::CUSTOMER) {
            $data['customer_name'] =  $sms_content['contact_name'];
        } else {
            $data['supplier_name'] =  $sms_content['contact_name'];
        }
        return $data;
    }


    public function getBulkSmsContactList()
    {
        $url_param = 'contact_type=' . $this->contactType .'&limit=' .$this->limit .'&offset=' . $this->offset;
        return $this->dueTrackerRepo->setPartner($this->partner)->getBulkSmsContactList($url_param);
    }

    public function sendBulkSmsThroughJob()
    {
        dispatch(new DueTrackerBulkSmsSend($this->partner, $this->contactIds, $this->contactType));
    }


    /**
     * @throws Exception
     */
    public function sendBulkSmsToContacts()
    {
        $sms_sending_lists = $this->getSmsContentsForBulkSmsSending();
        foreach ($sms_sending_lists as $each_sms) {
            $this->sendSMS($each_sms);
        }
    }

    public function getWebReportLink(int $partner_id, string $contact_id, string $contact_type): string
    {
        $reportLink = DueTrackerReportService::getWebReportLink($partner_id, $contact_id, $contact_type);
        return $reportLink ?? '';
    }

    /**
     * @throws WalletDebitForbiddenException
     * @throws InsufficientBalance|Exception
     */
    public function sendSmsForReminder(array $sms_content): bool
    {
        try {
            /* Todo need a partner resolver from id */
            $this->partner = Partner::where('id', $this->partnerId)->first();
            $sms_content['partner_name'] = $this->partner->name;
            $sms_content['partner_mobile'] = $this->partner->mobile;
            $sms_content['web_report_link'] = $this->getWebReportLink($this->partner->id,
                $sms_content['contact_id'], $this->contactType);
            $this->sendSMS($sms_content);
            return true;
        } catch (Exception $e){
            if ( $e instanceof InsufficientSmsForDueTrackerTagada) {
                return false;
            } else {
                throw $e;
            }
        }

    }

    /**
     * @throws AccountingEntryServerError
     * @throws ContactDoesNotExistInDueTracker
     */
    private function getSmsContentForSingleContact(): array
    {
        $contact_balance = $this->dueTrackerService
            ->setContactType($this->contactType)
            ->setContactId($this->contactId)
            ->setPartner($this->partner)
            ->getBalanceByContact();
        return [
            'balance' => $contact_balance['stats']['balance'],
            'balance_type' => $contact_balance['stats']['type'],
            'contact_name' => $contact_balance['contact_details']['name'],
            'contact_mobile' => $contact_balance['contact_details']['mobile'],
            'web_report_link' => $this->getWebReportLink($this->partner->id, $this->contactId, $this->contactType)
        ];
    }

    private function getPartnerInfo(): array
    {
        return [
            'name' => $this->partner->name,
            'avatar' => $this->partner->logo,
            'mobile' => $this->partner->mobile,
        ];
    }

    /**
     * @throws Exception
     */
    public function checkSmsBalanceAndSubscription(): string
    {

        $packageFeatureCount = app()->make(PackageFeatureCount::class)
            ->setPartnerId($this->partner->id)
            ->setFeature(PackageFeatureCount::SMS);

        $total_sms_count = 0;
        $user_count = 0;
        $sms_sending_lists = $this->getSmsContentsForBulkSmsSending();
        foreach ($sms_sending_lists as $sms_content) {
            $data = $this->generateSmsDataForContactType($sms_content);
            $sms = $this->getSmsHandler($data);
            $sms_estimation = $sms->getSmsCountAndEstimationCharge();
            $total_sms_count += $sms_estimation['sms_count'];
            $user_count++;
        }
        if ($packageFeatureCount->iseligible($total_sms_count)) {
            return en2bnNumber($user_count) . BulkSmsDialogue::FREE_SMS_DIALOGUE;
        } else {
           throw new InsufficientSmsForDueTrackerTagada();
        }
    }

    private function getSmsContentsForBulkSmsSending()
    {
        $bulk_sms_contents_by_contacts = $this->dueTrackerRepo
            ->setPartner($this->partner)
            ->getBulkSmsContactListByContactIds($this->contactType, $this->contactIds);

        foreach ($bulk_sms_contents_by_contacts as &$each_sms) {
            $each_sms['web_report_link'] = $this->getWebReportLink($this->partner->id,$each_sms['contact_id'],
                $this->contactType);
        }
        return $bulk_sms_contents_by_contacts;
    }
}