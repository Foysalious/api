<?php namespace Sheba\Reward\Helper;

use App\Models\Reward;

use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;

class TimeFrameCalculator
{
    private $reward;

    /**
     * @param Reward $reward
     * @return $this
     */
    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->reward->detail->timeline_type == constants('CAMPAIGN_REWARD_TIMELINE_TYPE')['Onetime']) {
            return $this->reward->end_time->isSameDay(Carbon::yesterday());
        } else {
            if ($this->reward->detail->timeline->cycle == "Daily") {
                $yesterday = strtolower(Carbon::yesterday()->format('l'));
                return property_exists($this->reward->detail->timeline->days, $yesterday);
            } elseif ($this->reward->detail->timeline->cycle == "Weekly") {
                return Carbon::yesterday()->isWeekend();
            } elseif ($this->reward->detail->timeline->cycle == "Monthly") {
                return Carbon::yesterday()->isLastOfMonth();
            }
        }
    }

    /**
     * @return TimeFrame
     */
    public function get()
    {
        if ($this->reward->detail->timeline_type == constants('CAMPAIGN_REWARD_TIMELINE_TYPE')['Onetime']) {
            return (new TimeFrame($this->reward->start_time, $this->reward->end_time));
        } else {
            if ($this->reward->detail->timeline->cycle == "Daily") {
                return (new TimeFrame())->forADay(Carbon::yesterday());
            } elseif ($this->reward->detail->timeline->cycle == "Weekly") {
                return (new TimeFrame())->forAWeek(Carbon::yesterday());
            } elseif ($this->reward->detail->timeline->cycle == "Monthly") {
                return (new TimeFrame())->forAMonth(Carbon::yesterday()->month, Carbon::yesterday()->year);
            }
        }
    }
}