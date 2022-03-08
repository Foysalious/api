<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerOrder;
use App\Repositories\PartnerOrderRepository;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\OrderRequestTransformer;
use App\Transformers\Partner\SubscriptionOrderRequestTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\PartnerOrderRequest\Statuses;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Jobs\JobStatuses;
use Sheba\ModificationFields;
use Sheba\PartnerOrderRequest\Creator;
use Sheba\PartnerOrderRequest\StatusChanger;
use Throwable;
use Illuminate\Validation\ValidationException;

class OrderRequestController extends Controller
{
    use ModificationFields;

    /** @var PartnerOrderRequestRepositoryInterface $orderRequestRepo */
    private $orderRequestRepo;
    /** @var StatusChanger $statusChanger */
    private $statusChanger;
    /** @var PartnerOrderRepository $partnerOrderRepository */
    private $partnerOrderRepository;
    /** @var SubscriptionOrderRequestRepositoryInterface */
    private $subscriptionOrderRequestRepo;

    public function __construct(PartnerOrderRequestRepositoryInterface $order_request_repo, StatusChanger $status_changer,
                                SubscriptionOrderRequestRepositoryInterface $subscription_order_request_repo)
    {
        $this->orderRequestRepo = $order_request_repo;
        $this->subscriptionOrderRequestRepo = $subscription_order_request_repo;
        $this->statusChanger = $status_changer;
        $this->partnerOrderRepository = new PartnerOrderRepository();
    }

    /**
     * @param $partner
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function lists($partner, Request $request, TimeFrame $time_frame)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:all,active,missed',
                'sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc,schedule_date,schedule_date:asc,schedule_date:desc'
            ]);

            $original_offset = $request->offset;
            $request->offset = 0;
            $partner = $request->partner;
            $start_end_date = $time_frame->forTodayAndYesterday();
            list($offset, $limit) = calculatePagination($request);
            list($order_by_field, $order_by_type) = $this->getSortByFieldFromOrderRequest($request);
            $order_requests_formatted = [];

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());

            /**
             * PARTNER ORDER REQUEST
             */
            $order_requests = $this->orderRequestRepo->getAllByPartnerWithFilter($partner, $start_end_date, $request->filter, $order_by_field, $order_by_type, $offset, $limit);
            $order_requests->each(function ($order_request) use ($manager, &$order_requests_formatted) {
                $resource = new Item($order_request, new OrderRequestTransformer());
                $order_requests_formatted[] = $manager->createData($resource)->toArray()['data'];
            });

            /**
             * SUBSCRIPTION ORDER REQUEST
             */
            $subscription_order_requests = $this->subscriptionOrderRequestRepo->getAllByPartnerWithFilter($partner, $start_end_date, $request->filter, $order_by_field, $order_by_type, $offset, $limit);
            $subscription_order_requests->each(function ($subscription_order_request) use ($manager, &$order_requests_formatted) {
                $resource = new Item($subscription_order_request, new SubscriptionOrderRequestTransformer());
                $order_requests_formatted[] = $manager->createData($resource)->toArray()['data'];
            });

            /**
             * OLD ORDER LISTS
             *
             */
            if ($request->filter == Statuses::MISSED) $orders = [];
            else $orders = $this->partnerOrderRepository->getNewOrdersWithJobs($request);

            $orders_with_order_requests = array_merge($orders, $order_requests_formatted);

            $sort_by = $order_by_type == 'asc' ? 'sortBy' : 'sortByDesc';
            $sorted_orders_with_order_requests = collect($orders_with_order_requests)
                ->$sort_by($order_by_field)
                ->slice($original_offset)
                ->take($request->limit);

            return api_response($request, null, 200, ['orders' => $sorted_orders_with_order_requests->values()]);
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
     * @param $request
     * @return array
     */
    private function getSortByFieldFromOrderRequest($request)
    {
        $order_by = 'desc';
        $field = 'created_at';
        if ($request->has('sort')) {
            $explode = explode(':', $request->get('sort'));
            $field = $explode[0] == 'created_at' ? $explode[0] : 'jobs.schedule_date';
            if (isset($explode[1]) && $explode[1] == 'asc') {
                $order_by = 'asc';
            }
        }
        return [$field, $order_by];
    }

    /**
     * @param $partner
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store($partner, Request $request, Creator $creator)
    {
        try {
            $this->validate($request, ['partners' => 'required', 'partner_order_id' => 'required']);
            $this->setModifier($request->partner);
            $partner_order = PartnerOrder::find($request->partner_order_id);
            $creator = $creator->setPartners($request->partners)->setPartnerOrder($partner_order);
            $creator->create();

            return api_response($request, null, 200, ['msg' => 'Successfully create partner order request']);
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
     * @param $partner
     * @param PartnerOrderRequest $partner_order_request
     * @param Request $request
     * @return JsonResponse
     */
    public function accept($partner, PartnerOrderRequest $partner_order_request, Request $request)
    {
        try {
            ini_set('memory_limit', '4096M');
            ini_set('max_execution_time', 660);
            $this->validate($request, ['resource_id' => 'int']);
            $partnerOrderId = $this->statusChanger->setPartnerOrderRequest($partner_order_request)->accept($request);
            if ($this->statusChanger->hasError()) return api_response($request, null, $this->statusChanger->getErrorCode(), [
                'message' => $this->statusChanger->getErrorMessage()
            ]);
            return api_response($request, null, 200, ['partner_order_id' => $partnerOrderId]);
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

    public function decline($partner, PartnerOrderRequest $partner_order_request, Request $request)
    {
        $this->statusChanger->setPartnerOrderRequest($partner_order_request)->decline($request);
        if ($this->statusChanger->hasError()) {
            return api_response($request, null, $this->statusChanger->getErrorCode(), [
                'message' => $this->statusChanger->getErrorMessage()
            ]);
        }
        return api_response($request, null, 200);
    }
}
