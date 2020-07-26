<?php

namespace App\Http\Controllers;

use App\Sheba\Loan\DLSV2\ExcelReport\LoanStatusReport;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Loan\ExcelReport\LoanDisbursementReport;


class LoanReportController extends Controller
{
    /**
     * @param Request $request
     * @param LoanDisbursementReport $report
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     */
    public function loanDisbursementReport(Request $request, LoanDisbursementReport $report)
    {
        try {
            $this->validate($request, [
                'start_date' => 'required',
                'end_date' => 'required',
            ]);
            return $report->setDates($request)->get();
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        }
    }

    public function loanStatusReport(Request $request, LoanStatusReport $report)
    {
        try {
            $this->validate($request, [
                'start_date' => 'date|required',
                'end_date' => 'date|required',
            ]);
            return $report->setDates($request)->get();
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        }

    }

}
