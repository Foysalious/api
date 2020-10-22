<?php namespace App\Http\Controllers\Webstore;

use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Helpers\TimeFrame;
use Sheba\Partner\Webstore\WebstoreDashboard;

class WebstoreDashboardController extends Controller
{
    public function getDashboard($partner, Request $request, WebstoreDashboard $webstoreDashboard, TimeFrame $time_frame)
    {
        $this->validate($request, [
            'frequency' => 'required|string|in:day,week,month,year',
            'date'      => 'required_if:frequency,day|date',
            'week'      => 'required_if:frequency,week|numeric',
            'month'     => 'required_if:frequency,month|numeric',
            'year'      => 'required_if:frequency,month,year|numeric',
        ]);
        $time_frame = $this->makeTimeFrame($request, $time_frame);
        $partner = Partner::find((int)$partner);
        $dashboard = $webstoreDashboard->setPartner($partner)->setTimeFrame($time_frame)->get();
        return api_response($request, null, 200, ['webstore_dashboard' => $dashboard]);
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
        return $time_frame;
    }
}
