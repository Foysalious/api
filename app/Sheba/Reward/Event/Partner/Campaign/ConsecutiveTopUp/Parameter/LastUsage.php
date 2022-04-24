<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Helpers\TimeFrame;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class LastUsage extends CampaignEventParameter
{
    public function check(Builder $query)
    {
        if ($this->value == null) return;

        $usage_timeframe = new TimeFrame($this->value->start . " 00:00:00", $this->value->end . " 23:59:59");

        $usage_calculator = new TopUpDayUsageCalculator($usage_timeframe);

        $query->whereIn('topup_orders.agent_id', $usage_calculator->getPartnerIdsMeetsCount($this->value->day_count, (bool)(int)$this->value->is_consecutive));
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("");
    }
}
