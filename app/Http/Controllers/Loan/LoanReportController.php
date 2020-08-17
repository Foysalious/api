<?php namespace App\Http\Controllers\Loan;

use App\Http\Controllers\Controller;
use App\Sheba\Loan\DLSV2\ExcelReport\LoanStatusReport;
use App\Sheba\Loan\DLSV2\ExcelReport\RetailerRegistrationReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Loan\ExcelReport\IPDCSmsSendingReport;
use Sheba\Loan\ExcelReport\LoanDisbursementReport;
use Sheba\Loan\ExcelReport\LoanDueReport;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Throwable;

class LoanReportController extends Controller
{
    /**
     * @param Request $request
     * @param LoanDisbursementReport $report
     * @return bool|JsonResponse
     * @throws NotAssociativeArray
     */
    public function loanDisbursementReport(Request $request, LoanDisbursementReport $report)
    {
        try {
            $this->validate($request, $this->reportValidator());
            return $report->setDates($request)->get();
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        }
    }

    /**
     * @param Request $request
     * @param IPDCSmsSendingReport $report
     * @return bool|JsonResponse
     * @throws NotAssociativeArray
     */
    public function ipdcSmsSendingReport(Request $request, IPDCSmsSendingReport $report)
    {
        try {
            $this->validate($request, $this->reportValidator());
            return $report->setDates($request)->get();
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        }

    }

    /**
     * @param Request $request
     * @param LoanDueReport $report
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     */
    public function loanDueReport(Request $request, LoanDueReport $report)
    {
        try {
            $this->validate($request, $this->reportValidator());
            return $report->setDates($request)->get();
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        }

    }

    /**
     * @return string[]
     */
    private function reportValidator()
    {
        return [
            'start_date' => 'required',
            'end_date' => 'required',
        ];
    }

    /**
     * @param Request $request
     * @param LoanStatusReport $report
     * @return bool|JsonResponse
     * @throws NotAssociativeArray
     */
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

    /**
     * @param Request $request
     * @param RetailerRegistrationReport $report
     * @return bool|JsonResponse
     * @throws NotAssociativeArray
     */
    public function retailerRegistrationReport(Request $request, RetailerRegistrationReport $report)
    {
        try {
            $this->validate($request, $this->reportValidator());
            return $report->setDates($request)->get();
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['data' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

}
