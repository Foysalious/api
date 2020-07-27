<?php

namespace Sheba\Loan\ExcelReport;

use Illuminate\Http\Request;
use Sheba\Dal\LoanPayment\Model as LoanRepayment;
use Sheba\Reports\ExcelHandler;

Class LoanDueReport
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
        $this->excelHandler->setName("Loan Due Report")->createReport($this->data)->download();
        return true;
    }

    private function makeData()
    {
        $repayments = $this->generateData();
        foreach ($repayments as $repayment){
            $this->data[] = [
                'sManager ID' => $repayment->loan->manager_id,
                'sManager Name' => $repayment->loan->manager_name,
                'sManager Phone Number' => $repayment->loan->manager_mobile,
                'Loan ID' => $repayment->loan->id,
                'Due Amount' => $repayment->credit,
                'Due Date' => $repayment->credit == 0 ? null : $repayment->created_at,
                'Repayment Amount' => $repayment->debit,
                'Repayment Date' => $repayment->debit == 0 ? null : $repayment->created_at,
                'Repayment Method' => $repayment->type
            ];
        }
    }

    /**
     * @return mixed
     */
    private function generateData()
    {
        return LoanRepayment::with([
            'loan' => function ($q) {
                $q->join('partners','partner_id','=', 'partners.id')
                    ->select('partner_bank_loans.id', 'loan_amount',
                        'partner_bank_loans.status', 'partner_bank_loans.created_at',
                        'partners.id as manager_id', 'partners.name as manager_name',
                        'partners.mobile as manager_mobile'
                    );
            },
            'loanClaim'
        ])->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->orderBy('id','desc')
            ->get();
    }

}