<?php namespace Sheba\PartnerAffiliation;

use Sheba\Repositories\PartnerAffiliationRepository;
use Sheba\PartnerAffiliation\PartnerAffiliationStatuses as Statuses;
use Sheba\PartnerAffiliation\PartnerAffiliationRejectReasons as RejectReasons;

class PartnerAffiliationHandler
{
    use HandlerSetters;

    private $affiliation;
    private $partner;

    private $repo;
    private $partnerStatuses;
    private $rejectAffiliationOnPartnerStatuses;
    private $closinghandler;

    public function __construct(PartnerAffiliationRepository $repo, PartnerAffiliationClosingHandler $closing_handler)
    {
        $this->partnerStatuses = constants('PARTNER_STATUSES');
        $this->rejectAffiliationOnPartnerStatuses = [
            $this->partnerStatuses['Closed'],
            $this->partnerStatuses['Blacklisted']
        ];
        $this->repo = $repo;
        $this->closinghandler = $closing_handler;
    }

    /**
     * @param $reason
     * @return void
     * @throws \Exception
     */
    public function reject($reason = null)
    {
        if($this->affiliation->status != Statuses::$pending) return;
        if($reason && !in_array($reason, RejectReasons::getKeys())) throw new \Exception('Invalid reason');
        $this->repo->update($this->affiliation, ['status' => Statuses::$rejected, 'reject_reason' => $reason]);
    }

    public function complete()
    {
        if($this->affiliation->status != Statuses::$pending) return;
        $this->closinghandler->setAffiliation($this->affiliation)->complete();
    }

    /**
     * @param $new_status
     * @throws \Exception
     */
    public function updatePartnerStatus($new_status)
    {
        if(!in_array($new_status, $this->rejectAffiliationOnPartnerStatuses)) return;
        $this->reject($new_status);
    }
}