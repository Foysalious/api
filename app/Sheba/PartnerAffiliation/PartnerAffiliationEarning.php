<?php namespace Sheba\PartnerAffiliation;

use App\Models\PartnerAffiliation;

interface PartnerAffiliationEarning
{
    public function partnerAffiliation(PartnerAffiliation $affiliation, $reward);
}