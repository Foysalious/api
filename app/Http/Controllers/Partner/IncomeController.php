<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\IncomeTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\Usage\Usage;
use Throwable;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    /** @var EntryRepository $entryRepo */
    private $entryRepo;

    public function __construct(EntryRepository $entry_repo)
    {
        $this->entryRepo = $entry_repo;
    }

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function index(Request $request, TimeFrame $time_frame)
    {
        try {
            $this->validate($request, [
                'frequency' => 'required|string|in:day,week,month,year',
                'date' => 'required_if:frequency,day|date',
                'week' => 'required_if:frequency,week|numeric',
                'month' => 'required_if:frequency,month|numeric',
                'year' => 'required_if:frequency,month,year|numeric',
            ]);
            list($offset, $limit) = calculatePagination($request);

            $time_frame = $time_frame->fromFrequencyRequest($request);
            $incomes_response = $this->entryRepo->setPartner($request->partner)
                ->setOffset($offset)
                ->setLimit($limit)
                ->setStartDate($time_frame->start)
                ->setEndDate($time_frame->end)
                ->getAllIncomesBetween();

            $final_incomes = [];
            $incomes_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            foreach ($incomes_response['incomes'] as $income) {
                $resource = new Item($income, new IncomeTransformer());
                $income_formatted = $manager->createData($resource)->toArray()['data'];
                $income_create_date = Carbon::parse($income_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_incomes[$income_create_date])) $final_incomes[$income_create_date] = [];
                array_push($final_incomes[$income_create_date], $income_formatted);
            }

            foreach ($final_incomes as $key => $value) {
                if (count($value) > 0) {
                    $income_list = ['date' => $key, 'incomes' => $value];
                    array_push($incomes_formatted, $income_list);
                }
            }

            return api_response($request, null, 200, [
                "total_income" => $incomes_response['total_income'],
                "total_due" => $incomes_response['total_due'],
                'time_frame' => [
                    'start' => $time_frame->start->toDateString(),
                    'end' => $time_frame->end->toDateString()
                ],
                'incomes' => $incomes_formatted
            ]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                'created_at' => 'required',
                'head_id' => 'required'
            ]);
            $input = $request->all(['amount', 'created_at', 'head_id', 'note']);
            $input['amount_cleared'] = $request->input('amount');
            $income = $this->entryRepo->setPartner($request->partner)->storeEntry(EntryType::getRoutable(EntryType::INCOME), $input);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($income, new IncomeTransformer());
            $income_formatted = $manager->createData($resource)->toArray()['data'];

            /**
             * USAGE LOG
             */
            (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_TRACKER_TRANSACTION)->create($request->manager_resource);
            return api_response($request, null, 200, ['income' => $income_formatted]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $income_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, $income_id, Request $request)
    {
        try {
            $income = $this->entryRepo->setPartner($request->partner)->showEntry(EntryType::getRoutable(EntryType::INCOME), $income_id);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($income, new IncomeTransformer());
            $income_formatted = $manager->createData($resource)->toArray()['data'];
            $income_formatted['is_editable'] = !in_array($income['head']['name'], AutomaticIncomes::heads());

            return api_response($request, $income, 200, ["income" => $income_formatted]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param $income_id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request,$partner, $income_id)
    {
        try {
            $input = $request->all(['amount', 'created_at', 'head_id', 'note']);
            $input['amount_cleared'] = $request->input('amount');
            $income = $this->entryRepo->setPartner($request->partner)->updateEntry(EntryType::getRoutable(EntryType::INCOME), $input, $income_id);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($income, new IncomeTransformer());
            $income_formatted = $manager->createData($resource)->toArray()['data'];

            return api_response($request, null, 200, ['income' => $income_formatted]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
