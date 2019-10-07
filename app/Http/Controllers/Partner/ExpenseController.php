<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Throwable;

class ExpenseController extends Controller
{
    /** @var EntryRepository $entryRepo */
    private $entryRepo;

    public function __construct(EntryRepository $entry_repo)
    {
        $this->entryRepo = $entry_repo;
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, []);
            $expenses = $this->entryRepo->setPartner($request->partner)->getAllExpenses();
            return api_response($request, null, 200, ['expenses' => $expenses]);
        } catch (ValidationException $e) {
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
