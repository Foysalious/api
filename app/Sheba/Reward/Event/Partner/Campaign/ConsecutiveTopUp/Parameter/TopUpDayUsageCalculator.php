<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Helpers\TimeFrame;

class TopUpDayUsageCalculator
{
    /** @var TimeFrame */
    private $timeFrame;

    public function __construct(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
    }

    public function getPartnerIdsMeetsCount($target_count, $is_consecutive)
    {
        $result = [];

        $partner_wise_dates = $this->getAllPartnerTopUpDates();
        foreach ($partner_wise_dates as $partner_id => $partner_dates) {
            if ($this->doesMeetCounts($partner_dates, $target_count, $is_consecutive)) $result[] = $partner_id;
        }

        return $result;
    }

    public function getPartnerIdsWithConsecutiveCount()
    {
        $result = [];

        $partner_wise_dates = $this->getAllPartnerTopUpDates();
        foreach ($partner_wise_dates as $partner_id => $partner_dates) {
            $result[$partner_id] = $this->getMaxConsecutiveStreak($partner_dates);
        }

        return $result;
    }

    private function getAllPartnerTopUpDates()
    {
        $result = [];
        $data = $this->getTopUpDates();
        foreach ($data as $item) {
            array_push_on_array($result, $item->agent_id, Carbon::parse($item->created_date));
        }
        return $result;
    }

    private function getTopUpDates()
    {
        return TopUpOrder::select('agent_id', \DB::raw('DATE(created_at) as created_date'))
            ->where('topup_orders.agent_type', Partner::class)
            ->where('topup_orders.status', Statuses::SUCCESSFUL)
            ->whereBetween('topup_orders.created_at', $this->timeFrame->getArray())
            ->groupBy('topup_orders.agent_id', 'created_date')
            ->get();
    }

    private function doesMeetCounts($dates, $target_count, $is_consecutive)
    {
        if ($is_consecutive) {
            return $this->hasNConsecutiveDates($dates, $target_count);
        } else {
            return count($dates) == $target_count;
        }
    }
    /**
     * @param Carbon[] $dates
     * @param $n
     * @return false
     */
    private function hasNConsecutiveDates($dates, $n)
    {
        $consecutive_count = 1;
        $length = count($dates);
        for ($i = 1; $i < $length; $i++) {
            if ($dates[$i]->isNextDay($dates[$i - 1])) $consecutive_count++;
            else $consecutive_count = 1;

            if ($consecutive_count == $n) return true;
        }
        return false;
    }

    private function getMaxConsecutiveStreak($dates)
    {
        $current_consecutive_count = 1;
        $max_consecutive_count = 1;

        $length = count($dates);
        for ($i = 1; $i < $length; $i++) {
            if ($dates[$i]->isNextDay($dates[$i - 1])) $current_consecutive_count++;
            else $current_consecutive_count = 1;

            $max_consecutive_count = max($max_consecutive_count, $current_consecutive_count);
        }
        return $max_consecutive_count;
    }
}