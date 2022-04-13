<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;

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
        $content = [
            'balance' => $contact_balance['stats']['balance'],
            'balance_type' => $contact_balance['stats']['type'],
            'contact_name' => $contact_balance['contact_details']['name']
        ];
        $partner_info = $this->dueTrackerService->getPartnerInfo($this->partner);
        $content['partner_name'] = $partner_info['name'];
        $content['web_report_link'] = 'www.google.com';
        return $content;
    }
}