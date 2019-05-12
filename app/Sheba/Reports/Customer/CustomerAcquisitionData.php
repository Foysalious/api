<?php namespace Sheba\Reports\Customer;

use Carbon\Carbon;
use Session;

class CustomerAcquisitionData extends CustomerData
{
    public function hasError()
    {
        if (($this->request->customer_acquisition_type == "weekly" && empty($this->request->acquisition_week))
            || ($this->request->customer_acquisition_type == "monthly" && empty($this->request->acquisition_month))) {
            flash()->error("Time frame can't be blank.");
            Session::flash('time_frame_error', true);
            Session::flash('customer_acquisition_error', true);
            Session::flash('customer_acquisition_type', $this->request->customer_acquisition_type);
            return true;
        }

        $timeLine = $this->calculateTimeFrame();
        if (Carbon::parse($timeLine['start_date']) > Carbon::now()) {
            flash()->error("You can't provide a time frame which is not available yet.");
            Session::flash('time_frame_error', true);
            Session::flash('customer_acquisition_error', true);
            Session::flash('customer_acquisition_type', $this->request->customer_acquisition_type);
            return true;
        }

        return false;
    }

    protected function calculateTimeFrame()
    {
        if ($this->request->customer_acquisition_type == "weekly") {
            $session_first_date = $this->request->week_start_date;
            $session_last_date = $this->request->week_end_date;
        } else if ($this->request->customer_acquisition_type == "monthly") {
            $month_data = explode('/', $this->request->acquisition_month);
            $days_in_this_month = cal_days_in_month(CAL_GREGORIAN, $month_data[0], $month_data[1]);
            $session_first_date = $month_data[1] . "-" . $month_data[0] . "-" . "01";
            $session_last_date = $month_data[1] . "-" . $month_data[0] . "-" . $days_in_this_month;
        } else if ($this->request->customer_acquisition_type == "daily") {
            $session_first_date = $this->request->acquisition_date;
            $session_last_date = $this->request->acquisition_date;
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