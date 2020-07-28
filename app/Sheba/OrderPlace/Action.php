<?php namespace Sheba\OrderPlace;


use App\Models\Partner;
use Carbon\Carbon;

class Action
{
    /** @var Partner */
    private $selectedPartner;
    /** @var Partner[] */
    private $partners;

    /**
     * @param Partner $partner
     * @return Action
     */
    public function setSelectedPartner($partner)
    {
        $this->selectedPartner = $partner;
        return $this;
    }

    /**
     * @param Partner[] $partners
     * @return Action
     */
    public function setPartners($partners)
    {
        $this->partners = $partners;
        return $this;
    }

    public function canAssignPartner()
    {
        if ($this->isLateNightOrder()) return false;
        if ($this->selectedPartner) return true;
        return false;
    }

    public function canSendPartnerOrderRequest()
    {
        if ($this->isLateNightOrder()) return false;
        if (count($this->partners) > 0) return true;
        return false;
    }

    private function isLateNightOrder()
    {
        $start = Carbon::parse('2:00 AM');
        $end = Carbon::parse('6:00 AM');
        return Carbon::now()->gte($start) && Carbon::now()->lte($end);
    }
}