<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;

class IncomeExpenseController extends Controller
{
    /** @var EntryRepository */
    private $entryRepo;

    public function __construct(EntryRepository $entry_repo)
    {
        $this->entryRepo = $entry_repo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ExpenseTrackingServerError
     */
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'frequency' => 'required|in:week,month,year,day'
            ]);
            $expenses = $this->entryRepo->setPartner($request->partner)->getAllExpenses();
            return api_response($request, null, 200, ['expenses' => $expenses]);
        } catch (ValidationException $e) {
            dd($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
