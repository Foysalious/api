<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;

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




    public function getSmsContentForTagada()
    {
        $content = $this->dueTrackerRepo
            ->setPartner($this->partner)
            ->getSmsContentForTagada($this->contact_type, $this->contact_id);
        $partner_info = $this->dueTrackerService->getPartnerInfo($this->partner);
        $content['partner_name'] = $partner_info['name'];
        $content['web_view_link'] = 'www.google.com';
        return $content;
    }
}