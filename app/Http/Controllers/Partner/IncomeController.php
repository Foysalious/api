<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $total_monthly_income = 1600.00;
            $total_monthly_due = 1200.00;
            $income_calculation = "[{\"date\":\"2019-09-04\",\"incomes\":[{\"id\":6777,\"expense_head\":\"travel_allowance\",\"expense_head_show_name\":{\"bn\":\"যাতায়াত ভাতা\",\"en\":\"Travel allowance\"},\"amount\":2000,\"note\":\"পাশা ভাইকে দেয়া হয়েছে\"},{\"id\":6776,\"expense_head\":\"food_allowance\",\"expense_head_show_name\":{\"bn\":\"খাবার ভাতা\",\"en\":\"Food allowance\"},\"amount\":2050,\"note\":null},{\"id\":6775,\"expense_head\":\"product_sale\",\"expense_head_show_name\":{\"bn\":\"পণ্য বিক্রয়\",\"en\":\"Product sales\"},\"amount\":3000,\"note\":\"Given to pasha vai\"}],\"created_by_name\":\"Resource-Md. Ashikul Alam Ashik\",\"created_at\":\"2019-09-04 07:01 PM\"},{\"date\":\"2019-09-05\",\"incomes\":[{\"id\":6780,\"expense_head\":\"travel_allowance\",\"expense_head_show_name\":{\"bn\":\"যাতায়াত ভাতা\",\"en\":\"Travel allowance\"},\"amount\":3000,\"note\":\"পাশা ভাইকে দেয়া হয়েছে\"},{\"id\":6781,\"expense_head\":\"food_allowance\",\"expense_head_show_name\":{\"bn\":\"খাবার ভাতা\",\"en\":\"Food allowance\"},\"amount\":6050,\"note\":null},{\"id\":6782,\"expense_head\":\"product_sale\",\"expense_head_show_name\":{\"bn\":\"পণ্য বিক্রয়\",\"en\":\"Product sales\"},\"amount\":5000,\"note\":\"Given to pasha vai\"}],\"created_by_name\":\"Resource-Md. Ashikul Alam Ashik\",\"created_at\":\"2019-09-05 06:01 PM\"},{\"date\":\"2019-09-06\",\"incomes\":[{\"id\":6797,\"expense_head\":\"travel_allowance\",\"expense_head_show_name\":{\"bn\":\"যাতায়াত ভাতা\",\"en\":\"Travel allowance\"},\"amount\":1000,\"note\":\"পাশা ভাইকে দেয়া হয়েছে\"},{\"id\":6796,\"expense_head\":\"food_allowance\",\"expense_head_show_name\":{\"bn\":\"খাবার ভাতা\",\"en\":\"Food allowance\"},\"amount\":5050,\"note\":null},{\"id\":6795,\"expense_head\":\"product_sale\",\"expense_head_show_name\":{\"bn\":\"পণ্য বিক্রয়\",\"en\":\"Product sales\"},\"amount\":2000,\"note\":\"Given to pasha vai\"}],\"created_by_name\":\"Resource-Md. Ashikul Alam Ashik\",\"created_at\":\"2019-09-06 04:01 PM\"}]";
            $income_calculation = json_decode($income_calculation);
            return api_response($request, $income_calculation, 200, ["total_monthly_income" => $total_monthly_income,
                "total_monthly_due" => $total_monthly_due,"incomes" => $income_calculation]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($customer, $income_id, Request $request)
    {
        try {
            $income_calculation = "";
            $income_calculation = json_decode($income_calculation);
            return $income_calculation;
//          return api_response($request, $income_calculation, 200, ["incomes" => $income_calculation]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
