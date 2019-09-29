<?php namespace Sheba\PartnerAffiliation;

use App\Models\Partner;
use App\Models\PartnerAffiliation;

trait HandlerSetters
{
    public function setAffiliation(PartnerAffiliation $affiliation)
    {
        $this->affiliation = $affiliation;
        $this->partner = $affiliation->partner;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->affiliation = $partner->affiliation;
        return $this;
    }
}