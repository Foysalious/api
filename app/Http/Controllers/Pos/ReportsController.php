<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Reports\Pos\PosReportRepository;
use Throwable;

class ReportsController extends Controller
{
    /**
     * @var PosReportRepository
     */
    private $repository;

    public function __construct(PosReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function product(Request $request)
    {
        try {
            if ($request->has('download_excel')) {
                $name = 'Product Wise Sales Report';
                return $this->repository->getProductWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadExcel($name);
            } elseif ($request->has('download_pdf')) {
                $name = 'Product Wise Sales Report';
                $template = 'pos_product_wise_sales';
                return $this->repository->getProductWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadPdf($name, $template);
            } else {
                $data = $this->repository->getProductWise()->prepareQuery($request, $request->partner)->prepareData()->getData();
                return api_response($request, $data, 200, ['result' => $data]);
            }
        } catch (ValidationException $e) {
            $errorMessage = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $errorMessage]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function customer(Request $request)
    {
        try {
            if ($request->has('download_excel')) {
                $name = 'Customer Wise Sales Report';
                return $this->repository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadExcel($name);
            } elseif ($request->has('download_pdf')) {
                $name = 'Customer Wise Sales Report';
                $template = 'pos_customer_wise_sales';
                return $this->repository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData(false)->downloadPdf($name, $template);
            } else {
                $data = $this->repository->getCustomerWise()->prepareQuery($request, $request->partner)->prepareData()->getData();
                return api_response($request, $data, 200, ['result' => $data]);
            }
        } catch (ValidationException $e) {
            $errorMessage = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $errorMessage]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
