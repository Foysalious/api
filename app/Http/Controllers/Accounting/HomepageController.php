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
        $startDate = $this->convertStartDate($request->start_date);
        $endDate = $this->convertEndDate($request->end_date);

        if ($endDate < $startDate){
            return api_response($request,null, 400, ['message' => 'End date can not smaller than start date']);
        }

        try {
            $response = $this->homepageRepo->getIncomeExpenseBalance($request->partner->id, $startDate, $endDate);
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
        $nextCursor = $request->next_cursor ?? null;
        $startDate = $request->has('start_date') ? $this->convertStartDate($request->start_date) : null;
        $endDate = $request->has('start_date') ? $this->convertEndDate($request->end_date) : null;
        $sourceType = $request->has('root_account') ? $request->source_type : null;
        try {
            $response = $this->homepageRepo->getIncomeExpenseEntries($request->partner->id, $limit, $nextCursor, $startDate, $endDate, $sourceType);
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
     * @param $accountKey
     * @param Request $request
     * @return JsonResponse
     */
    public function getEntriesByAccountKey($accountKey, Request $request): JsonResponse
    {
        $limit = $request->limit ?? 15;
        $nextCursor = $request->next_cursor ?? null;
        try {
            $response = $this->homepageRepo->getEntriesByAccountKey($accountKey, $request->partner->id, $limit, $nextCursor);
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
    public function getAccountListBalance(Request $request)
    {
        $startDate = $request->has('start_date') ? $this->convertStartDate($request->start_date) : null;
        $endDate = $request->has('start_date') ? $this->convertEndDate($request->end_date) : null;
        $limit = $request->has('limit') ? $request->limit : null;
        $offset = $request->has('offset') ? $request->offset : null;
        $rootAccount = $request->has('root_account') ? $request->root_account : null;
        if ($endDate < $startDate){
            return api_response($request,null, 400, ['message' => 'End date can not smaller than start date']);
        }

        try {
            $response = $this->homepageRepo->getAccountListBalance($request->partner->id, $startDate, $endDate, $limit, $offset, $rootAccount);
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

    public function getTrainingVideo(Request $request)
    {
        try {
            $response = [
                'video_key' => "accounting_dashboard",
                'faq_url' => 'https://faq.sheba.xyz/dummy'
            ];
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

    public function getTimeFilters(Request $request)
    {
        Carbon::setWeekStartsAt(Carbon::SATURDAY);
        Carbon::setWeekEndsAt(Carbon::FRIDAY);
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfQuarter = Carbon::now()->startOfQuarter();
        $endOfQuarter = Carbon::now()->endOfQuarter();
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $response = [
            [
                'title' => 'আজ (' . convertNumbersToBangla(Carbon::now()->day, false) . ' ' . banglaMonth(Carbon::now()->month) . ')',
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
            ],
            [
                'title' => 'এই সপ্তাহ (' .
                    convertNumbersToBangla($startOfWeek->day, false) .
                    ($startOfWeek->month === $endOfWeek->month ? '' : banglaMonth($startOfWeek->month)). ' - ' .
                    convertNumbersToBangla($endOfWeek->day, false) .' '.
                    banglaMonth($endOfWeek->month) . ')',
                'start_date' => $startOfWeek->format('Y-m-d'),
                'end_date' => $endOfWeek->format('Y-m-d'),
            ],
            [
                'title' => 'এই মাস (' . banglaMonth($startOfMonth->month) . ' মাস)',
                'start_date' => $startOfMonth->format('Y-m-d'),
                'end_date' => $endOfMonth->format('Y-m-d'),
            ],
            [
                'title' => 'এই কোয়ার্টার (' . banglaMonth($startOfQuarter->month) .' - '. banglaMonth($endOfQuarter->month) .')',
                'start_date' => $startOfQuarter->format('Y-m-d'),
                'end_date' => $endOfQuarter->format('Y-m-d'),
            ],
            [
                'title' => 'এই বছর (' . convertNumbersToBangla($startOfYear->year, false) . ' সাল)',
                'start_date' => $startOfYear->format('Y-m-d'),
                'end_date' => $endOfYear->format('Y-m-d'),
            ],
        ];
        return api_response($request, null, 200, ['data' => $response]);
    }

    private function convertStartDate($date) {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 0:00:00')->timestamp :
            strtotime('1 January 1971');
    }

    private function convertEndDate($date) {
        return $date ?
            Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 23:59:59')->timestamp :
            strtotime('tomorrow midnight') - 1;
    }
}