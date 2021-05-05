<?php namespace App\Http\Controllers\Accounting;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Sheba\AccountingEntry\Repository\HomepageRepository;

class HomepageController extends Controller
{
    private $homepageRepo;

    public function __construct(HomepageRepository $homepageRepo)
    {
        $this->homepageRepo = $homepageRepo;
    }

    public function getAssetAccountBalance(Request $request)
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

    public function getIncomeExpenseBalance(Request $request)
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
}