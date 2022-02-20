<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\PartnerSale\PartnerSale;
use Sheba\Helpers\TimeFrame;
use Throwable;

class SalesStatisticsController extends Controller
{
    /**
     * @param Request $request
     * @param PartnerSale $sale
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function index(Request $request, PartnerSale $sale, TimeFrame $time_frame)
    {
        $this->validate($request, [
            'frequency' => 'required|string|in:day,week,month,year',
            'date'      => 'required_if:frequency,day|date',
            'week'      => 'required_if:frequency,week|numeric',
            'month'     => 'required_if:frequency,month|numeric',
            'year'      => 'required_if:frequency,month,year|numeric',
        ]);

        $time_frame = $this->makeTimeFrame($request, $time_frame);
        $sale = $sale->setParams($request->frequency)->setTimeFrame($time_frame)->setPartner($request->partner)->get();

        return api_response($request, $sale, 200, ['data' => $sale]);
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
