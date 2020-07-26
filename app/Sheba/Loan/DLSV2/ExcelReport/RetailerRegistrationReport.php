<?php


namespace App\Sheba\Loan\DLSV2\ExcelReport;


use App\Models\Partner;
use App\Models\PartnerBankLoan;
use Illuminate\Http\Request;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;

class RetailerRegistrationReport
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
     * @throws NotAssociativeArray
     * @throws Exception
     */
    public function get()
    {
        $this->makeData();
        $this->excelHandler->setName("Retailer Registration Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {
        $robi_retailers = Partner::whereHas('retailers', function ($q)  {
            $q->where('retailers.strategic_partner_id', 2);
        })->get();

    }

}