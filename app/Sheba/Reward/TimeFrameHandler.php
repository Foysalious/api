<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;

class TimeFrameHandler
{
    public function isValid(Reward $reward)
    {
        if ($reward->detail->timeline_type == constants('CAMPAIGN_REWARD_TIMELINE_TYPE')['Onetime']) {
            return $reward->end_time->isSameDay(Carbon::yesterday());
        } else {
            if ($reward->detail->timeline->cycle == "Daily") {
                $yesterday = strtolower(Carbon::yesterday()->format('l'));
                return property_exists($reward->detail->timeline->days, $yesterday);
            } elseif ($reward->detail->timeline->cycle == "Weekly") {
                return Carbon::yesterday()->isWeekend();
            } elseif ($reward->detail->timeline->cycle == "Monthly") {
                return Carbon::yesterday()->isLastOfMonth();
            }
        }
    }

    public function get(Reward $reward)
    {
        if ($reward->detail->timeline_type == constants('CAMPAIGN_REWARD_TIMELINE_TYPE')['Onetime']) {
            return (new TimeFrame($reward->start_time, $reward->end_time));
        } else {
            if ($reward->detail->timeline->cycle == "Daily") {
                return (new TimeFrame())->forADay(Carbon::yesterday());
            } elseif ($reward->detail->timeline->cycle == "Weekly") {
                return (new TimeFrame())->forAWeek(Carbon::yesterday());
            } elseif ($reward->detail->timeline->cycle == "Monthly") {
                return (new TimeFrame())->forAMonth(Carbon::yesterday()->month, Carbon::yesterday()->year);
            }
        }
    }
}