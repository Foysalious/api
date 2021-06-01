<?php namespace App\Http\Controllers\Accounting;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Reports\Accounting\AccountingReportRepository;
use Sheba\Reports\Pos\PosReportRepository;
use Throwable;

class ReportsController extends Controller
{
    /**
     * @var PosReportRepository
     */
    private $posReportRepository;
    private $accountingReportRepository;

    public function __construct(PosReportRepository $posRepository, AccountingReportRepository $accountingReportRepository)
    {
        $this->posReportRepository = $posRepository;
        $this->accountingReportRepository = $accountingReportRepository;
    }

    public function getCustomerWiseReport(Request $request) {
        try {
            if ($request->has('download_excel')) {
                $name = 'Customer Wise Sales Report';
                return $this->posReportRepository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadExcel($name);
            } elseif ($request->has('download_pdf')) {
                $name = 'Customer Wise Sales Report';
                $template = 'pos_customer_wise_sales';
                return $this->posReportRepository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadPdf($name, $template);
            } else {
                $data = $this->posReportRepository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData()->getData();
                return api_response($request, $data, 200, ['result' => $data]);
            }
        } catch (ValidationException $e) {
            $errorMessage = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $errorMessage]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }

    }

    public function getAccountingReport(Request $request, $reportType)
    {
        $report_types = [ "profit_loss_report", "journal_report", "balance_sheet_report", "general_ledger_report" ];

        if (in_array($reportType, $report_types)) {
            try {
                $response = $this->accountingReportRepository->getAccountingReport($reportType, $request->partner->id, $request->start_date, $request->end_date);
                return api_response($request, $response, 200, ['data' => $response]);
            } catch (Exception $e) {
                return api_response(
                    $request,
                    null,
                    $e->getCode() == 0 ? 400 : $e->getCode(),
                    ['message' => $e->getMessage()]
                );
            }
        }
        return api_response($request, null, 402, ['message' => 'Please apply with correct report type.']);
    }
}