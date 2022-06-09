<?php namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\TopUp\TopUpCounts;

class TopupDayCount extends ActionEventParameter
{
    public function validate(){}

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if ($this->value == null) return true;

        /** @var TopUpOrder $topup_order */
        $topup_order = $params[0];

        if (!TopUpCounts::isFirstTopUpTodayForAgent($topup_order)) return false;

        $day_count = TopUpCounts::getDayCountBeforeTodaysTopUp($topup_order);

        return $day_count >= $this->value->min && $day_count <= $this->value->max;
    }
}