<?php namespace Sheba\PartnerAffiliation;

use App\Models\PartnerAffiliation;
use Sheba\Repositories\PartnerAffiliationRepository;
use Sheba\PartnerAffiliation\PartnerAffiliationStatuses as Statuses;

class PartnerAffiliationClosingHandler
{
    use HandlerSetters;

    private $affiliation;
    private $partner;

    private $repo;
    private $rewardHandler;

    private $orderBenchmark;

    public function __construct(PartnerAffiliationRepository $repo, RewardHandler $rewardHandler)
    {
        $this->repo = $repo;
        $this->orderBenchmark = PartnerAffiliation::partnerOrderBenchmark();
        $this->rewardHandler = $rewardHandler;
    }

    public function complete()
    {
        if(!$this->validate()) return;
        $this->updateAffiliationStatus();
        $this->rewardHandler->setPartner($this->partner)->setAffiliation($this->affiliation)->orderCompleted();
    }

    private function validate()
    {
        return $this->partner->orders()->whereNotNull('closed_at')->count() == $this->orderBenchmark;
    }

    private function updateAffiliationStatus()
    {
        $this->repo->update($this->affiliation, ['status' => Statuses::$successful]);
    }
}