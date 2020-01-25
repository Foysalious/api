<?php namespace Sheba\PartnerList;


use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Sheba\Dal\SubscriptionOrder\Cycles;

class SubscriptionPartnerListBuilder extends PartnerListBuilder
{
    private $cycleType;

    public function buildServiceQuery(EloquentBuilder $query)
    {
        parent::buildServiceQuery($query);

        if ($this->isWeeklySubscription()) {
            $query->where('partner_service.is_weekly_subscription_enable', 1);
        }
        if ($this->isMonthlySubscription()) {
            $query->where('partner_service.is_monthly_subscription_enable', 1);
        }
    }

    public function setCycleType($type)
    {
        $this->cycleType = $type;
        return $this;
    }

    public function isWeeklySubscription()
    {
        return $this->cycleType == Cycles::WEEKLY;
    }

    public function isMonthlySubscription()
    {
        return $this->cycleType == Cycles::MONTHLY;
    }
}
