<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class PerformanceController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $this->validate($request, ['timeline' => 'required|string|in:time-frame,week,month,year']);

            $data = [
                'total_sales'       => 5000.00,
                'order_accepted'    => 50,
                'order_completed'   => 50
            ];

            if ($request->frequency == 'time-frame') {
                $data['day'] = 'Thursday, Oct 31';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
            }

            if ($request->frequency == 'week') {
                $data['timeline'] = 'Oct 26 - Nov 1';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
                $data['sales_stat_breakdown'] = [
                    ['value' => 'Sun', 'amount' => 455.58],
                    ['value' => 'Mon', 'amount' => 4552],
                    ['value' => 'Tue', 'amount' => 45005],
                    ['value' => 'Wed', 'amount' => 4505,],
                    ['value' => 'Thu', 'amount' => 455],
                    ['value' => 'Fri', 'amount' => 4550],
                    ['value' => 'Sat', 'amount' => 455]
                ];
                $data['order_stat_breakdown'] = [
                    ['value' => 'Sun', 'amount' => 455],
                    ['value' => 'Mon', 'amount' => 4552],
                    ['value' => 'Tue', 'amount' => 45005],
                    ['value' => 'Wed', 'amount' => 4505],
                    ['value' => 'Thu', 'amount' => 455],
                    ['value' => 'Fri', 'amount' => 4550],
                    ['value' => 'Sat', 'amount' => 455]
                ];
            }

            if ($request->frequency == 'month') {
                $data['timeline'] = 'October';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
                $data['sales_stat_breakdown'] = [
                    ['value' => 1, 'amount' => 11.22],
                    ['value' => 2, 'amount' => 1121],
                    ['value' => 3, 'amount' => 112.2],
                    ['value' => 4, 'amount' => 11],
                    ['value' => 5, 'amount' => 11]
                ];
                $data['order_stat_breakdown'] = [
                    ['value' => 1, 'amount' => 10],
                    ['value' => 2, 'amount' => 22],
                    ['value' => 3, 'amount' => 11],
                    ['value' => 4, 'amount' => 111],
                    ['value' => 5, 'amount' => 101]
                ];
            }

            if ($request->frequency == 'year') {
                $data['timeline'] = 'Year 2018';
                $data['lifetime_sales'] = 483.56;
            }

            return api_response($request, $data, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}