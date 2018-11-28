<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Helpers\TimeFrame;


class PerformanceController extends Controller
{
    public function index($partner, Request $request, PartnerPerformance $performance)
    {
        try {
            $this->validate($request, [
                'of' => 'required|string|in:time-frame,week,month',
                'start_date' => 'required_if:of,time-frame|date',
                'end_date' => 'required_if:of,time-frame|date',
                'week' => 'required_if:of,week|numeric',
                'month' => 'required_if:of,month|numeric',
                'year' => 'required_if:of,month|numeric',
            ]);

            $time_frame = $this->getTimeFrame($request);
            $performance->setPartner($request->partner)->setTimeFrame($time_frame)->calculate();

            $data = [
                'timeline' => $time_frame->start->toDateString() . ' - ' . $time_frame->end->toDateString()
            ] + $performance->getData()->toArray();

            return api_response($request, $performance, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return TimeFrame
     */
    private function getTimeFrame(Request $request)
    {
        $time_frame = new TimeFrame();
        if($request->of == "week") {
            $time_frame->forSomeWeekFromNow($request->week);
        } else if ($request->of == "month") {
            $time_frame->forAMonth($request->month, $request->year);
        } else {
            $time_frame->set($request->start_date, $request->end_date);
        }
        return $time_frame;
    }
}