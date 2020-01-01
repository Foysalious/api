<?php namespace Sheba\Referral\Referrers;

use Illuminate\Support\Collection;
use Sheba\Referral\HasReferrals;
use Sheba\Referral\Referrer;
use Sheba\Referral\ReferrerInterface;

class Partner extends Referrer implements ReferrerInterface
{
    public function __construct(HasReferrals $referrer)
    {
        $this->referrer = $referrer;
    }

    function getReferrals(): Collection
    {
        $this->refers = $this->init()->selectRaw('`partners`.`id`,`partners`.`name`')->leftJoin('partner_usages_history', 'partners.id', '=', 'partner_usages_history.partner_id')->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->groupBy('partners.id')->get();
        foreach ($this->refers as $refer) {
            $refer['contact_number'] = $refer->getContactNumber();
            $refer['milestone']      = getMilestoneForPartner($refer->usages);
        }
        return $this->refers;
    }
}
