<?php namespace Sheba\Reports\Customer;

use App\Models\Order;
use Carbon\Carbon;
use Session;

class UniqueCustomerData extends CustomerData
{
    public function get()
    {
        $time_line = $this->calculateTimeFrame();
        $orders = Order::query();
        $orders = $this->notLifetimeQuery($orders, $time_line);

        $customers = $this->getCustomers(null, $orders->pluck('customer_id')->unique()->toArray());

        $customers->map(function ($customer) use ($time_line) {
            $start_date = Carbon::parse($time_line['start_date']);
            $end_date = Carbon::parse($time_line['end_date'])->addDay()->subSecond();
            $customer->is_returning = !($customer->created_at->between($start_date, $end_date));
            return $customer;
        });

        return $customers;
    }

    public function hasError()
    {
        if (($this->request->unique_customer_type == "weekly" && empty($this->request->unique_customer_week))
            || ($this->request->unique_customer_type == "monthly" && empty($this->request->unique_customer_month))) {
            flash()->error("Time frame can't be blank.");
            Session::flash('time_frame_error', true);
            Session::flash('unique_customer_error', true);
            Session::flash('unique_customer_type', $this->request->unique_customer_type);
            return true;
        }

        $time_line = $this->calculateTimeFrame();
        if (Carbon::parse($time_line['start_date']) > Carbon::now()) {
            flash()->error("You can't provide a time frame which is not available yet.");
            Session::flash('time_frame_error', true);
            Session::flash('unique_customer_error', true);
            Session::flash('unique_customer_type', $this->request->unique_customer_type);
            return true;
        }

        return false;
    }

    protected function calculateTimeFrame()
    {
        if ($this->request->unique_customer_type == "weekly") {
            $session_first_date = $this->request->week_start_date;
            $session_last_date = $this->request->week_end_date;
        } else if ($this->request->unique_customer_type == "monthly") {
            $month_data = explode('/', $this->request->unique_customer_month);
            $days_in_this_month = cal_days_in_month(CAL_GREGORIAN, $month_data[0], $month_data[1]);
            $session_first_date = $month_data[1] . "-" . $month_data[0] . "-" . "01";
            $session_last_date = $month_data[1] . "-" . $month_data[0] . "-" . $days_in_this_month;
        } else if ($this->request->unique_customer_type == "daily") {
            $session_first_date = $this->request->unique_customer_date;
            $session_last_date = $this->request->unique_customer_date;
        } else {
            $session_first_date = $this->request->start_date;
            $session_last_date = $this->request->end_date;
        }

        return [
            'start_date' => $session_first_date,
            'end_date' => $session_last_date,
        ];
    }
}