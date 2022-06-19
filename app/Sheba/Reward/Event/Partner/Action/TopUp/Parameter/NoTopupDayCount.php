<?php namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\TopUp\TopUpCounts;

class NoTopupDayCount extends ActionEventParameter
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

        return TopUpCounts::getNoTopUpDayBeforeTodaysTopUp($topup_order) >= $this->value;
    }
}