<?php namespace Sheba\PartnerAffiliation;

use App\Models\Partner;
use App\Models\PartnerAffiliation;
use Sheba\Bondhu\AffiliateEarning;
use Sheba\Repositories\PartnerRepository;

class RewardHandler
{
    use HandlerSetters;

    private $partnerRepo;
    private $affiliateEarnings;
    private $baseReward;
    private $reward;

    private $partner;
    private $affiliation;

    public function __construct(PartnerRepository $partner_repo, AffiliateEarning $affiliate_earning)
    {
        $this->partnerRepo = $partner_repo;
        $this->affiliateEarnings = $affiliate_earning;
        $this->baseReward = PartnerAffiliation::reward();
    }

    /**
     * LOGIC CHANGE - PARTNER REWARD MOVE TO WAITING STATUS
     *
     * public function onBoarded()
    {
        $this->setReward(constants('PARTNER_AFFILIATION_REWARD_BREAKDOWN.on_boarded'));
        $this->handle();
    }*/

    public function waiting()
    {
        $this->setReward(constants('PARTNER_AFFILIATION_REWARD_BREAKDOWN.waiting'));
        $this->handle();
    }

    public function Verified()
    {
        $this->setReward(constants('PARTNER_AFFILIATION_REWARD_BREAKDOWN.verified'));
        $this->handle();
    }

    public function orderCompleted()
    {
        $this->setReward(constants('PARTNER_AFFILIATION_REWARD_BREAKDOWN.order_completed'));
        $this->handle();
    }

    private function handle()
    {
        if (!$this->isCapableForReward()) return ;
        $this->updateAffiliateIncome();
        $this->updatePartnerAcquisitionCost();
    }

    private function isCapableForReward()
    {
        if (!(empty($this->partner) || empty($this->affiliation))) {
            return $this->affiliation->status == PartnerAffiliationStatuses::$pending;
        }
        return false;
    }

    private function updateAffiliateIncome()
    {
        $this->affiliateEarnings->partnerAffiliation($this->affiliation, $this->reward);
    }

    private function updatePartnerAcquisitionCost()
    {
        $this->partnerRepo->update($this->partner, ['affiliation_cost' => $this->partner->affiliation_cost + $this->reward]);
    }

    private function setReward($percent)
    {
        $this->reward = floatval($this->baseReward) * (floatval($percent)/100);
    }
}