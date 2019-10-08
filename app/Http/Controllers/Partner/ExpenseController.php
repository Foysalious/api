<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\IncomeTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\Helpers\TimeFrame;
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
                'date'      => 'required_if:frequency,day|date',
                'week'      => 'required_if:frequency,week|numeric',
                'month'     => 'required_if:frequency,month|numeric',
                'year'      => 'required_if:frequency,month,year|numeric',
            ]);
            list($offset, $limit) = calculatePagination($request);

            $time_frame = $time_frame->makeTimeFrame($request);
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
                $resource = new Item($expense, new IncomeTransformer());
                $expense_formatted = $manager->createData($resource)->toArray()['data'];
                $expense_create_date = Carbon::parse($expense_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_incomes[$expense_create_date])) $final_incomes[$expense_create_date] = [];
                array_push($final_incomes[$expense_create_date], $expense_formatted);
            }

            foreach ($final_incomes as $key => $value) {
                if (count($value) > 0) {
                    $expense_list = [
                        'date' => $key, 'incomes' => $value
                    ];
                    array_push($expenses_formatted, $expense_list);
                }
            }

            return api_response($request, null, 200, [
                "total_expense" => $expenses_response['total_expense'],
                "total_due" => $expenses_response['total_due'],
                'expenses' => $expenses_formatted
            ]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
