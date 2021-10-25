<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PosCustomer;
use Illuminate\Support\Facades\DB;
use App\Transformers\CustomSerializer;
use App\Transformers\ExpenseTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\Usage\Usage;
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
            $expenses_response = $this->entryRepo->setPartner($request->partner)
                ->setOffset($offset)
                ->setLimit($limit)
                ->setStartDate($time_frame->start)
                ->setEndDate($time_frame->end)
                ->getAllExpensesBetween();

            $final_incomes = [];
            $expenses_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            foreach ($expenses_response['expenses'] as $expense) {
                $resource = new Item($expense, new ExpenseTransformer());
                $expense_formatted = $manager->createData($resource)->toArray()['data'];
                $expense_create_date = Carbon::parse($expense_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_incomes[$expense_create_date])) $final_incomes[$expense_create_date] = [];
                array_push($final_incomes[$expense_create_date], $expense_formatted);
            }

            foreach ($final_incomes as $key => $value) {
                if (count($value) > 0) {
                    $expense_list = [
                        'date' => $key, 'expenses' => $value
                    ];
                    array_push($expenses_formatted, $expense_list);
                }
            }

            return api_response($request, null, 200, [
                "total_expense" => $expenses_response['total_expense'],
                "total_due" => $expenses_response['total_due'],
                'time_frame' => [
                    'start' => $time_frame->start->toDateString(),
                    'end' => $time_frame->end->toDateString()
                ],
                'expenses' => $expenses_formatted
            ]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'created_at' => 'required',
            'head_id' => 'required',
            'amount_cleared' => 'sometimes|required|numeric',
            'customer_id' => 'required_with:amount_cleared'
        ]);

        $input = $request->all(['amount', 'created_at', 'head_id', 'note']);
        $input['amount_cleared'] = $request->has('amount_cleared') ? $request->input('amount_cleared') : $request->input('amount');

        $customer_id = $request->input('customer_id');
        if ($customer_id) $input['profile_id'] = PosCustomer::find($customer_id)->profile_id;

        $expense = $this->entryRepo->setPartner($request->partner)->storeEntry(EntryType::getRoutable(EntryType::EXPENSE), $input);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($expense, new ExpenseTransformer());
        $expense_formatted = $manager->createData($resource)->toArray()['data'];

        /**
         * USAGE LOG
         */
        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::EXPENSE_TRACKER_TRANSACTION)->create($request->manager_resource);
        return api_response($request, null, 200, ['expense' => $expense_formatted]);
    }

    /**
     * @param $partner
     * @param $expense_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, $expense_id, Request $request)
    {
        try {
            $expense = $this->entryRepo->setPartner($request->partner)->showEntry(EntryType::getRoutable(EntryType::EXPENSE), $expense_id);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($expense, new ExpenseTransformer());
            $expense_formatted = $manager->createData($resource)->toArray()['data'];

            $expense_formatted['customer'] = null;
            $expense_formatted['is_editable'] = !in_array($expense['head']['name'], AutomaticExpense::heads());
            if (isset($expense['party']['profile_id'])) {
                $pos_customer = PosCustomer::with('profile')->where('profile_id', $expense['party']['profile_id'])->first();
                $expense_formatted['customer'] = ['id' => $pos_customer->id, 'name' => $pos_customer->profile->name];
            }

            return api_response($request, $expense, 200, ["expense" => $expense_formatted]);
        } catch (ExpenseTrackingServerError $e) {
            $message = $e->getMessage();
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    /**
     * @param Request $request
     * @param $partner
     * @param $expense_id
     * @return JsonResponse
     */
    public function update(Request $request, $partner, $expense_id)
    {
        $this->validate($request, ['amount_cleared' => 'sometimes|required|numeric', 'customer_id' => 'required_with:amount_cleared']);
        $input = $request->all(['amount', 'created_at', 'head_id', 'note']);

        if ($request->input('amount_cleared'))
            $input['amount_cleared'] = $request->input('amount_cleared');

        $customer_id = $request->input('customer_id');
        if ($customer_id) $input['profile_id'] = PosCustomer::find($customer_id)->profile_id;

        $expense = $this->entryRepo->setPartner($request->partner)->updateEntry(EntryType::getRoutable(EntryType::EXPENSE), $input, $expense_id);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($expense, new ExpenseTransformer());
        $expense_formatted = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['expense' => $expense_formatted]);
    }
}
