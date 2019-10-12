<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PosCustomer;
use App\Models\Profile;
use App\Transformers\CustomSerializer;
use App\Transformers\ExpenseTransformer;
use App\Transformers\PayableItemTransformer;
use App\Transformers\PayableTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ModificationFields;
use Throwable;
use Illuminate\Http\JsonResponse;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;

class PayableController extends Controller
{
    use ModificationFields;

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
            $this->validate($request, []);
            list($offset, $limit) = calculatePagination($request);
            $payables_generator = $this->entryRepo->setPartner($request->partner)->setOffset($offset)->setLimit($limit);
            if ($request->has('customer_id') && $request->customer_id) {
                $profile_id = PosCustomer::find($request->customer_id)->profile_id;
                $payables_response = $payables_generator->getAllPayablesByCustomer((int)$profile_id);
            } else {
                $payables_response = $payables_generator->getAllPayables();
            }

            $profiles_id = array_unique(array_column(array_column($payables_response['payables'], 'party'), 'profile_id'));
            $profiles = Profile::whereIn('id', $profiles_id)->get()->pluckMultiple(['name', 'pro_pic'], 'id')->toArray();
            $pos_customers = PosCustomer::whereIn('profile_id', $profiles_id)->pluck('id', 'profile_id')->toArray();

            $final_payables = [];
            $payables_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());

            foreach ($payables_response['payables'] as $payables) {
                $resource = new Item($payables, new PayableTransformer());
                $payable_formatted = $manager->createData($resource)->toArray()['data'];

                $profile_id = $payable_formatted['profile_id'];
                $payable_formatted['customer'] = [
                    'id' => $pos_customers[$profile_id],
                    'name' => $profiles[$profile_id]['name'],
                    'image' => $profiles[$profile_id]['pro_pic']
                ];

                $payables_create_date = Carbon::parse($payable_formatted['created_at'])->format('Y-m-d');
                unset($payable_formatted['profile_id']);

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
                "total_payable" => $payables_response['total_payables'], 'payables' => $payables_formatted
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
     * @param $partner_id
     * @param $payable_id
     * @return JsonResponse
     */
    public function show(Request $request, $partner_id, $payable_id)
    {
        try {
            $payable_generator = $this->entryRepo->setPartner($request->partner);
            $profile_id = PosCustomer::find($request->customer_id)->profile_id;

            $payable = $payable_generator->getPayableById((int)$profile_id, $payable_id);
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($payable, new PayableItemTransformer());
            $payable_formatted = $manager->createData($resource)->toArray()['data'];

            return api_response($request, $payable_formatted, 200, ["payable" => $payable_formatted]);
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
    public function logs(Request $request)
    {
        try {
            $logs = [
                ['id' => 1, 'amount' => 200, 'updated_by' => 'Test Name 1', 'updated_date' => '2019-10-15', 'updated_time' => '10:20 AM'],
                ['id' => 2, 'amount' => 2400, 'updated_by' => 'Test Name 2', 'updated_date' => '2019-10-22', 'updated_time' => '10:30 AM'],
                ['id' => 3, 'amount' => 2040, 'updated_by' => 'Test Name 3', 'updated_date' => '2019-10-01', 'updated_time' => '10:21 AM'],
                ['id' => 4, 'amount' => 20450, 'updated_by' => 'Test Name 4', 'updated_date' => '2019-10-11', 'updated_time' => '10:20 AM'],
            ];

            return api_response($request, null, 200, ['logs' => $logs]);
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
     * @param $partner_id
     * @param $payable_id
     * @return JsonResponse
     */
    public function pay(Request $request, $partner_id, $payable_id)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric', 'customer_id'=> 'required|numeric']);

            $input = $request->only(['amount']);
            $input['profile_id'] = PosCustomer::find($request->customer_id)->profile_id;
            $updater_information = [
                'updated_by' => $request->manager_resource->id,
                'updated_by_type' => get_class($request->manager_resource),
                'updated_by_name' => $request->manager_resource->profile->name
            ];

            $payable = $this->entryRepo->setPartner($request->partner)->payPayable($input, $updater_information, (int)$payable_id);
            return api_response($request, null, 200, ['payable' => $payable]);
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
