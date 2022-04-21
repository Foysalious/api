<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Helpers\TimeFrame;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class LastUsage extends CampaignEventParameter
{
    public function check(Builder $query)
    {
        if ($this->value == null) return;

        $query->whereIn('topup_orders.agent_id', $this->getPartnerIds());
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("");
    }

    private function getPartnerIds()
    {
        $result = [];

        $partner_wise_dates = $this->getAllPartnerTopUpDates();
        foreach ($partner_wise_dates as $partner_id => $partner_dates) {
            if ($this->doesMeetCounts($partner_dates)) $result[] = $partner_id;
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
        $usage_timeframe = new TimeFrame($this->value->start . " 00:00:00", $this->value->end . " 23:59:59");

        return TopUpOrder::select('agent_id', \DB::raw('DATE(created_at) as created_date'))
            ->where('topup_orders.agent_type', Partner::class)
            ->where('topup_orders.status', Statuses::SUCCESSFUL)
            ->whereBetween('topup_orders.created_at', $usage_timeframe->getArray())
            ->groupBy('topup_orders.agent_id', 'created_date')
            ->get();
    }

    private function doesMeetCounts($dates)
    {
        if ((int)$this->value->is_consecutive) {
            return $this->hasNConsecutiveDates($dates, $this->value->day_count);
        } else {
            return count($dates) == $this->value->day_count;
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
            if ($dates[$i]->diffInDays($dates[$i-1]) == -1) $consecutive_count++;
            else $consecutive_count = 1;

            if ($consecutive_count == $n) return true;
        }
        return false;
    }
}
