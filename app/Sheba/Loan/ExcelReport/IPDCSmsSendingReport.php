<?php

namespace Sheba\Loan\ExcelReport;

use Illuminate\Http\Request;
use Sheba\Dal\IPDCSmsLog\Model as IPDCSmsLog;
use Sheba\Reports\ExcelHandler;

Class IPDCSmsSendingReport
{
    private $data;
    private $excelHandler;
    private $start_date;
    private $end_date;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data         = [];
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setDates(Request $request)
    {
        $this->start_date = $request->start_date;
        $this->end_date   = $request->end_date;
        return $this;
    }

    /**
     * @return bool
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     * @throws \Exception
     */
    public function get()
    {
        $this->makeData();
        $this->excelHandler->setName("Sms Sending Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {
        $sms_log = $this->generateData();
        foreach ($sms_log as $sms){
            $this->data[] = [
                'sManager ID' => $sms->partner ? $sms->partner->id : null,
                'sManager Name' => $sms->partner ? $sms->partner->name : null,
                'sManager Phone Number' => $sms->partner ? $sms->partner->mobile : null,
                'Loan ID' => $sms->loan ? $sms->loan->id : null,
                'SMS Sent At' => $sms->created_at
            ];
        }
    }

    /**
     * @return mixed
     */
    private function generateData()
    {
        return IPDCSmsLog::with([
            'partner','loan'
        ])->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->where('used_on_type', "App\\Models\\PartnerBankLoan")
            ->orderBy('id','desc')
            ->get();
    }

}