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


    /**
     * @return bool
     */
    public function canAssignPartner()
    {
        return $this->selectedPartner && !$this->isLateNightOrder();
    }

    /**
     * @return bool
     */
    public function canSendPartnerOrderRequest()
    {
        if ($this->selectedPartner) return false;
        if ($this->isLateNightOrder()) return false;
        if (count($this->partners) > 0) return true;
        return false;
    }

    public function isLateNightOrder()
    {
        $start = Carbon::parse('12:00 AM');
        $end = Carbon::parse('7:00 AM');
        return Carbon::now()->gte($start) && Carbon::now()->lte($end);
    }
}