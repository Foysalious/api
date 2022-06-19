<?php namespace Sheba\Reward\Event\Partner\Action;

use App\Models\PartnerUsageHistory;

trait PartnerFilter
{
    /**
     * @return bool
     */
    private function filterByUserFilters(): bool
    {
        if (!$this->filterStatus()) return false;
        if (!$this->filterActiveStatus()) return false;
        if (!$this->filterRegistrationWithin()) return false;
        return true;
    }

    private function filterStatus(): bool
    {
        $user_filters = $this->reward->getUserFilters();
        if (!array_key_exists("status", $user_filters)) return true;

        return $this->partner->status == $user_filters['status'];
    }

    private function filterActiveStatus(): bool
    {
        $active_time_frame = $this->reward->getActiveStatusUserFilterTimeFrame();
        if ($active_time_frame == null) return true;

        return PartnerUsageHistory::where('partner_id', $this->partner->id)
            ->whereBetween('created_at', $active_time_frame->getArray())
            ->count() > 0;
    }

    private function filterRegistrationWithin(): bool
    {
        $registration_within = $this->reward->getRegistrationWithinUserFilterTimeFrame();

        if ($registration_within == null) return true;

        return $this->partner->created_at->between($registration_within->start, $registration_within->end);
    }
}