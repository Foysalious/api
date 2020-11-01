<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Transformers\CustomSerializer;
use App\Transformers\ReceivableTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\Repository\StatsRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Throwable;
use Illuminate\Http\JsonResponse;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;

class IncomeExpenseController extends Controller
{
    use ModificationFields;

    /** @var EntryRepository */
    private $entryRepo;
    /** @var StatsRepository */
    private $statsRepo;

    public function __construct(EntryRepository $entry_repo, StatsRepository $stats_repo)
    {
        $this->entryRepo = $entry_repo;
        $this->statsRepo = $stats_repo;
    }

    /**
     * @param Request $request
     * @param PartnerRepositoryInterface $partner_repo
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function index(Request $request, PartnerRepositoryInterface $partner_repo, TimeFrame $time_frame)
    {
        try {
            $this->validate($request, ['frequency' => 'required|in:week,month,year,day']);
            if (!$request->partner->expense_account_id) {
                $account = $this->entryRepo->createExpenseUser($request->partner);
                $this->setModifier($request->partner);
                $data = ['expense_account_id' => $account['id']];
                $partner_repo->update($request->partner, $data);
            }

            $expenses = $this->statsRepo->setPartner($request->partner)->between($time_frame->fromFrequencyRequest($request));

            return api_response($request, null, 200, ['expenses' => $expenses['data']]);
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
            $this->validate($request, []);
            list($offset, $limit) = calculatePagination($request);
            $receivables_response = $this->entryRepo->setPartner($request->partner)->setOffset($offset)->setLimit($limit)->getAllReceivables();

            $profiles_id = array_unique(array_column(array_column($receivables_response['receivables'], 'party'), 'profile_id'));
            $profiles = Profile::whereIn('id', $profiles_id)->pluck('name', 'id')->toArray();

            $final_receivables = [];
            $receivables_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            foreach ($receivables_response['receivables'] as $receivables) {
                $resource = new Item($receivables, new ReceivableTransformer());
                $payable_formatted = $manager->createData($resource)->toArray()['data'];
                $payable_formatted['name'] = empty($profiles) ? null : $profiles[$payable_formatted['profile_id']];
                $receivables_create_date = Carbon::parse($payable_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_receivables[$receivables_create_date])) $final_receivables[$receivables_create_date] = [];
                array_push($final_receivables[$receivables_create_date], $payable_formatted);
            }

            foreach ($final_receivables as $key => $value) {
                if (count($value) > 0) {
                    $receivable_list = [
                        'date' => $key, 'receivables' => $value
                    ];
                    array_push($receivables_formatted, $receivable_list);
                }
            }

            return api_response($request, null, 200, [
                "total_receivable" => $receivables_response['total_receivables'], 'receivables' => $receivables_formatted
            ]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
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
    public function getHeads(Request $request)
    {
        try {
            $this->validate($request, ['for' => 'required|in:income,expense']);
            $heads_response = $this->entryRepo->setPartner($request->partner)->getHeads($request->for);
            return api_response($request, null, 200, ["heads" => $heads_response['heads']]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
