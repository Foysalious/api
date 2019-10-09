<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use App\Transformers\CustomSerializer;
use App\Transformers\PayableTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
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
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ProfileRepository $profile_repo
     * @return JsonResponse
     */
    public function payable(Request $request)
    {
        try {
            $this->validate($request, []);
            list($offset, $limit) = calculatePagination($request);
            $payables_response = $this->entryRepo->setPartner($request->partner)
                ->setOffset($offset)
                ->setLimit($limit)
                ->getAllPayables();

            $profiles_id = array_unique(array_column(array_column($payables_response['payables'], 'party'), 'profile_id'));
            $profiles = Profile::whereIn('id', $profiles_id)->pluck('name', 'id')->toArray();

            $final_payables = [];
            $payables_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            foreach ($payables_response['payables'] as $payables) {
                $resource = new Item($payables, new PayableTransformer());
                $payable_formatted = $manager->createData($resource)->toArray()['data'];
                $payable_formatted['name'] = $profiles[$payable_formatted['profile_id']];
                $payables_create_date = Carbon::parse($payable_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_payables[$payables_create_date])) $final_payables[$payables_create_date] = [];
                array_push($final_payables[$payables_create_date], $payable_formatted);
            }

            foreach ($final_payables as $key => $value) {
                if (count($value) > 0) {
                    $payable_list = [
                        'date' => $key, 'payables' => $value
                    ];
                    array_push($payables_formatted, $payable_list);
                }
            }

            return api_response($request, null, 200, [
                "total_payable" => $payables_response['total_payables'],
                'payables' => $payables_formatted
            ]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function receivable(Request $request)
    {
        try {
            $this->validate($request, [
                'frequency' => 'required|in:week,month,year,day'
            ]);
            $expenses = $this->entryRepo->setPartner($request->partner)->getAllExpenses();
            return api_response($request, null, 200, ['expenses' => $expenses]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
