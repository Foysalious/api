<?php namespace App\Http\Controllers\Accounting;

use Carbon\Carbon;
use Exception;
use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\HomepageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    private $homepageRepo;

    public function __construct(HomepageRepository $homepageRepo)
    {
        $this->homepageRepo = $homepageRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAssetAccountBalance(Request $request): JsonResponse
    {
        try {
            $response = $this->homepageRepo->getAssetBalance($request->partner->id);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIncomeExpenseBalance(Request $request): JsonResponse
    {
        try {
            $response = $this->homepageRepo->getIncomeExpenseBalance($request->partner->id, $request->start_date, $request->end_date);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIncomeExpenseEntries(Request $request): JsonResponse
    {
        $limit = $request->limit ?? 10;
        try {
            $response = $this->homepageRepo->getIncomeExpenseEntries($request->partner->id, $limit);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDueCollectionBalance(Request $request):JsonResponse
    {
        $startDate = $this->convertStartDate($request->start_date);
        $endDate = $this->convertEndDate($request->end_date);
        if ($endDate < $startDate){
            return api_response($request,null, 400, ['message' => 'End date can not smaller than start date']);
        }

        try {
            $response = $this->homepageRepo->getDueCollectionBalance($request->partner->id, $startDate, $endDate);
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountListBalance(Request $request): JsonResponse
    {
        $startDate = $this->convertStartDate($request->start_date);
        $endDate = $this->convertEndDate($request->end_date);
        $limit = $request->limit ?? 10;
        if ($endDate < $startDate){
            return api_response($request,null, 400, ['message' => 'End date can not smaller than start date']);
        }

        try {
            $response = $this->homepageRepo->getAccountListBalance($request->partner->id, $startDate, $endDate, $limit);
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

    private function convertStartDate($date) {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 0:00:00')->timestamp :
            strtotime('today midnight');
    }

    private function convertEndDate($date) {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 23:59:59')->timestamp :
            strtotime('tomorrow midnight') - 1;
    }
}