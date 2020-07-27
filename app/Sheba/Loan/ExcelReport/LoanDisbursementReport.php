<?php

namespace Sheba\Loan\ExcelReport;

use Illuminate\Http\Request;
use Sheba\Reports\ExcelHandler;
use Sheba\Dal\LoanClaimRequest\Model as LoanClaimRequest;

Class LoanDisbursementReport
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
        $this->excelHandler->setName("Loan Disbursement Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {
        $loans_claims = $this->generateData();
        foreach ($loans_claims as $claim){
            $this->data[] = [
                'sManager ID' => $claim->loan->manager_id,
                'sManager Name' => $claim->loan->manager_name,
                'sManager Phone Number' => $claim->loan->manager_mobile,
                'Loan ID' => $claim->loan->id,
                'Loan Status' => $claim->loan->status,
                'Disbursement Date' => $claim->loan->created_at,
                'Disbursement Amount' => $claim->loan->loan_amount,
                'Claim Date' => $claim->created_at,
                'Claim Amount' => $claim->amount
            ];
        }
    }

    /**
     * @return mixed
     */
    private function generateData()
    {
        return LoanClaimRequest::with([
            'loan' => function ($q) {
                $q->join('partners','partner_id','=', 'partners.id')
                    ->select('partner_bank_loans.id', 'loan_amount',
                        'partner_bank_loans.status', 'partner_bank_loans.created_at',
                        'partners.id as manager_id', 'partners.name as manager_name',
                        'partners.mobile as manager_mobile'
                    );
            }
        ])->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->orderBy('id','desc')
            ->get();
    }

}