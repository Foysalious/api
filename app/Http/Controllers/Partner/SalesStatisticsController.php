<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalesStatisticsController extends Controller
{
    public function getSales($partner, Request $request)
    {
        try {
            $this->validate($request, ['frequency' => 'required|string|in:day,week,month,year']);

            $data = [
                'total_sales'       => 5000.00,
                'order_accepted'    => 50,
                'order_completed'   => 50
            ];

            if ($request->frequency == 'day') {
                $data['day'] = 'Thursday, Oct 31';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
            }

            if ($request->frequency == 'week') {
                $data['timeline'] = 'Oct 26 - Nov 1';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
                $data['sales_stat_breakdown'] = ['Sun' => 455.58, 'Mon' => 4552, 'Tue' => 45005, 'Wed' => 4505, 'Thu' => 455, 'Fri' => 4550, 'Sat' => 4550];
                $data['order_stat_breakdown'] = ['Sun' => 455, 'Mon' => 4552, 'Tue' => 45005, 'Wed' => 4505, 'Thu' => 455, 'Fri' => 4550, 'Sat' => 4550];
            }

            if ($request->frequency == 'month') {
                $data['timeline'] = 'October';
                $data['sheba_payable'] = 4832.56;
                $data['partner_collection'] = 483.56;
                $data['sales_stat_breakdown'] = ['1' => 11.2, '2' => 11, '3' => 11.55, '4' => 11, '5' => 11, '6' => 11, '7' => 11, '8' => 11, '9' => 11, '10' => 11, '11' => 11, '12' => 11, '13' => 11, '14' => 11, '15' => 11, '16' => 11, '17' => 11, '18' => 11, '19' => 11, '20' => 11, '21' => 11, '22' => 11, '23' => 11, '24' => 11, '25' => 11, '26' => 113.56, '27' => 11, '28' => 11, '29' => 11, '30' => 11, '31' => 11.22];
                $data['order_stat_breakdown'] = ['1' => 11, '2' => 11, '3' => 11, '4' => 11, '5' => 11, '6' => 11, '7' => 11, '8' => 11, '9' => 11, '10' => 11, '11' => 11, '12' => 11, '13' => 11, '14' => 11, '15' => 11, '16' => 11, '17' => 11, '18' => 11, '19' => 11, '20' => 11, '21' => 11, '22' => 11, '23' => 11, '24' => 11, '25' => 11, '26' => 113.56, '27' => 11, '28' => 11, '29' => 11, '30' => 11, '31' => 11.22];
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