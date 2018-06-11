<?php namespace Sheba\PartnerAffiliation;

use App\Models\PartnerAffiliation;
use Sheba\Bondhu\AffiliateEarning;
use Sheba\Repositories\PartnerAffiliationRepository;
use Sheba\Repositories\PartnerRepository;
use Sheba\PartnerAffiliation\PartnerAffiliationStatuses as Statuses;

class PartnerAffiliationClosingHandler
{
    use HandlerSetters;

    private $affiliation;
    private $partner;

    private $repo;
    private $partnerRepo;
    private $affiliateEarnings;

    private $reward;
    private $orderBenchmark;

    public function __construct(PartnerAffiliationRepository $repo, PartnerRepository $partner_repo, AffiliateEarning $affiliate_earning)
    {
        $this->repo = $repo;
        $this->partnerRepo = $partner_repo;
        $this->affiliateEarnings = $affiliate_earning;
        $this->reward = PartnerAffiliation::reward();
        $this->orderBenchmark = PartnerAffiliation::partnerOrderBenchmark();
    }

    public function complete()
    {
        if(!$this->validate()) return;
        $this->updateAffiliationStatus();
        $this->updateAffiliateIncome();
        $this->updatePartnerAcquisitionCost();
    }

    private function validate()
    {
        return $this->partner->orders()->whereNotNull('closed_at')->count() == $this->orderBenchmark;
    }

    private function updateAffiliationStatus()
    {
        $this->repo->update($this->affiliation, ['status' => Statuses::$successful]);
    }

    private function updateAffiliateIncome()
    {
        $this->affiliateEarnings->partnerAffiliation($this->affiliation, $this->reward);
    }

    private function updatePartnerAcquisitionCost()
    {
        $this->partnerRepo->update($this->partner, ['acquisition_cost' => $this->reward]);
    }
}