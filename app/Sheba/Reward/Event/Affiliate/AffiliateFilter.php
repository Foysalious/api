<?php namespace Sheba\Reward\Event\Affiliate;


use Sheba\Dal\RewardAffiliates\Model as RewardAffiliates;

trait AffiliateFilter
{
    /**
     * @return bool
     */
    private function filterConstraints(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    private function filterTargets(): bool
    {
        return RewardAffiliates::where('affiliate', $this->affiliate->id)
                ->where('reward', $this->reward->id)
                ->count() > 0;
    }

    /**
     * @return bool
     */
    private function filterByUserFilters(): bool
    {
        if (!$this->filterRegistrationWithin()) return false;
        return true;
    }

    private function filterRegistrationWithin(): bool
    {
        $registration_within = $this->reward->getRegistrationWithinUserFilterTimeFrame();

        if ($registration_within == null) return true;

        return $this->affiliate->created_at->between($registration_within->start, $registration_within->end);
    }
}