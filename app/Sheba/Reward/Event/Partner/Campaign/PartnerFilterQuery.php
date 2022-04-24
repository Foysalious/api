<?php namespace Sheba\Reward\Event\Partner\Campaign;

use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerUsageHistory;

trait PartnerFilterQuery
{
    private function filterPartnersInQuery()
    {
        $this->filterSubscription();
        $this->filterStatus();
        $this->filterActiveStatus();
        $this->filterRegistrationWithin();
    }

    private function filterSubscription()
    {
        $constraints = $this->reward->constraints->groupBy('constraint_type');

        if (!array_key_exists(PartnerSubscriptionPackage::class, $constraints)) return;

        $ids = $constraints[PartnerSubscriptionPackage::class]->pluck('constraint_id')->toArray();
        $this->query->whereIn('partners.package_id', $ids);
    }

    private function filterStatus()
    {
        $user_filters = $this->reward->getUserFilters();
        if (!array_key_exists("status", $user_filters)) return;

        $this->query->where('partners.status', $user_filters['status']);
    }

    private function filterActiveStatus()
    {
        $active_time_frame = $this->reward->getActiveStatusUserFilterTimeFrame();
        if ($active_time_frame == null) return;

        $active_partners = PartnerUsageHistory::whereBetween('created_at', $active_time_frame->getArray())->groupBy('partner_id')->pluck('partner_id');
        $this->query->where('partners.id', $active_partners);
    }

    private function filterRegistrationWithin()
    {
        $registration_within = $this->reward->getRegistrationWithinUserFilterTimeFrame();

        if ($registration_within == null) return;

        $this->query->whereBetween('partners.created_at', $registration_within->getArray());
    }
}