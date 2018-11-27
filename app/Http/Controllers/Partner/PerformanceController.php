<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class PerformanceController extends Controller
{
    public function index($partner, Request $request)
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

            $performance = [
                'timeline' => 'Oct 26 - Nov 1',
                'performance_summary' => [
                    'total_order_taken' => 51,
                    'successfully_completed' => 39,
                    'order_without_complain' => 30,
                    'timely_order_taken' => 46,
                    'timely_job_start' => 15
                ],
                'successfully_completed' => [
                    'total_order' => 24,
                    'rate' => 49,
                    'last_week_rate' => 34,
                    'is_improved' => 1,
                    'last_week_rate_difference' => 15
                ],
                'order_without_complain' => [
                    'total_order' => 30,
                    'rate' => 60,
                    'last_week_rate' => 54,
                    'is_improved' => 1,
                    'last_week_rate_difference' => 6
                ],
                'timely_order_taken' => [
                    'total_order' => 46,
                    'rate' => 93,
                    'last_week_rate' => 95,
                    'is_improved' => 0,
                    'last_week_rate_difference' => 2
                ],
                'timely_job_start' => [
                    'total_order' => 15,
                    'rate' => 30,
                    'last_week_rate' => 47,
                    'is_improved' => 0,
                    'last_week_rate_difference' => 17
                ]
            ];
            return api_response($request, $performance, 200, ['data' => $performance]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}