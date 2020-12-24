<?php namespace Sheba\Analysis\ExpenseIncome;

use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;

class ExpenseIncome
{
    private $partner, $timeFrame, $frame, $request;

    public function __construct(TimeFrame $timeFrame)
    {
        $this->timeFrame = $timeFrame;
    }

    /**
     * @param mixed $request
     * @return ExpenseIncome
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return ExpenseIncome
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $frame
     * @return ExpenseIncome
     */
    public function setFrame($frame)
    {
        $this->frame = $frame;
        return $this;
    }

    public function dashboard()
    {
        list($breakdowns, $time_frame) = $this->breakdowns($this->request->get('frequency'));
        return [
            'sales_point_income' => 5000000,
            'sheba_income' => 20000,
            'other_income' => 1000,
            'expense' => 2000,
            'profit' => 9000,
            'payable' => 20000,
            'receivable' => 100000,
            'breakdowns' => $breakdowns,
            'time_frame' => $time_frame
        ];
    }

    private function breakdowns($frequency)
    {
        $frequencies = ['week' => 7, 'month' => 30, 'year' => 365, 'day' => 24];
        $data = [];
        foreach (range(1, $frequencies[$frequency]) as $index) {
            $data[] = ['index' => $index, 'income' => rand(10, 10000000), 'expense' => rand(0, 100000)];
        }
        return [$data, $this->makeTimeFrame($this->request, $this->timeFrame)];
    }

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return TimeFrame
     */
    private function makeTimeFrame(Request $request, TimeFrame $time_frame)
    {
        switch ($request->frequency) {
            case "day":
                $date = Carbon::parse($request->date);
                $time_frame = $time_frame->forADay($date);
                break;
            case "week":
                $time_frame = $time_frame->forSomeWeekFromNow($request->week);
                break;
            case "month":
                $time_frame = $time_frame->forAMonth($request->month, $request->year);
                break;
            case "year":
                $time_frame = $time_frame->forAYear($request->year);
                break;
            default:
                echo "Invalid time frame";
        }
        if (isset($time_frame)) {
            $time_frame=$time_frame->getArray();
            $time_['start'] = $time_frame[0]->format('Y-m-d');
            $time_['end'] = $time_frame[1]->format('Y-m-d');
            return $time_;
        }
        return $time_frame;
    }
}
