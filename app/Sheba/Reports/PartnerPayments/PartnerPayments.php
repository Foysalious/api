<?php namespace Sheba\Reports\PartnerPayments;

use App\Http\Requests\SPPaymentReportRequest;
use Carbon\Carbon;
use Sheba\Reports\ReportData;

abstract class PartnerPayments extends ReportData
{
    /** @var SPPaymentReportRequest */
    protected $request;
    /** @var array */
    protected $session;

    public function setRequest(SPPaymentReportRequest $request)
    {
        $this->request = $request;
        $this->setSession();
        return $this;
    }

    /**
     * @return array
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Calculate the start date and end date for the payable session.
     */
    protected function setSession()
    {
        $cur_month = Carbon::now()->month;
        $cur_date = Carbon::now()->day;
        $cur_year = Carbon::now()->year;

        if ($this->request->payable_session == 'current') {
            $days_in_this_month = cal_days_in_month(CAL_GREGORIAN, $cur_month, $cur_year);
            if ($cur_date <= 15) {
                $session_first_date = $cur_year . "-" . $cur_month . "-" . "01";
                $session_last_date = $cur_year . "-" . $cur_month . "-" . "15";
            } else {
                $session_first_date = $cur_year . "-" . $cur_month . "-" . "16";
                $session_last_date = $cur_year . "-" . $cur_month . "-" . $days_in_this_month;
            }
        } else {
            if ($cur_date <= 15) {
                $pre_month = Carbon::now()->subMonth()->month;
                $pre_year = Carbon::now()->subMonth()->year;
                $days_in_prev_month = cal_days_in_month(CAL_GREGORIAN, $pre_month, $pre_year);
                $session_first_date = $pre_year . "-" . $pre_month . "-" . "16";
                $session_last_date = $pre_year . "-" . $pre_month . "-" . $days_in_prev_month;
            } else {
                $session_first_date = $cur_year . "-" . $cur_month . "-" . "01";
                $session_last_date = $cur_year . "-" . $cur_month . "-" . "15";
            }
        }

        $this->session = [
            'start_date' => $session_first_date,
            'end_date' => $session_last_date,
        ];
    }

}