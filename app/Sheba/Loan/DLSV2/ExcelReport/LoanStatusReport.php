<?php


namespace App\Sheba\Loan\DLSV2\ExcelReport;


use App\Models\PartnerBankLoan;
use Exception;
use Illuminate\Http\Request;
use Sheba\Dal\LoanClaimRequest\Model as LoanClaimRequest;
use Sheba\Loan\ExcelReport\LoanDisbursementReport;
use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;

class LoanStatusReport
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
        $this->excelHandler->setName("Loan Status Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {
        $rejected_loans = PartnerBankLoan::TypeAndStatus('micro','declined')->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->orderBy('id','desc')
            ->get();

        foreach ($rejected_loans as $rejected_loan){
            $contactResource = $rejected_loan->partner->getContactResource();
            $this->data[] = [
                'User Phone Number' => $contactResource->profile->mobile,
                'User Name' => $contactResource->profile->name,
                'Shop Name' => $rejected_loan->partner->name,
                'Loan Status' => $rejected_loan->status,
                'Loan application submission date ' => $rejected_loan->created_at,
                'Rejection cause' =>  $rejected_loan->rejectedLog() ? $rejected_loan->rejectedLog()->description : null,
                'Rejection date' =>  $rejected_loan->rejectedLog() ? $rejected_loan->rejectedLog()->created_at : null,
            ];
        }
    }

}