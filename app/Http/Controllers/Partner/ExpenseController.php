<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\IncomeTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\EntryType;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric', 'created_at' => 'required', 'head_id' => 'required']);
            $input = $request->only(['amount', 'created_at', 'head_id', 'note']);
            $input['amount_cleared'] = $request->input('amount_cleared') ?
                $request->input('amount_cleared') : $request->input('amount');
            $expense = $this->entryRepo->setPartner($request->partner)->storeEntry(EntryType::getRoutable(EntryType::EXPENSE), $input);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($expense, new IncomeTransformer());
            $expense_formatted = $manager->createData($resource)->toArray()['data'];

            return api_response($request, null, 200, ['expense' => $expense_formatted]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors());
            return api_response($request, $message, 400, ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $expense_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($partner, $expense_id, Request $request)
    {
        try {
            $expense = $this->entryRepo->setPartner($request->partner)->showEntry(EntryType::getRoutable(EntryType::EXPENSE), $expense_id);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($expense, new IncomeTransformer());
            $expense_formatted = $manager->createData($resource)->toArray()['data'];

            return api_response($request, $expense, 200, ["expense" => $expense_formatted]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
