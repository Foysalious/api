<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\OrderRequestTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\PartnerOrderRequest\StatusChanger;
use Throwable;
use Illuminate\Validation\ValidationException;

class OrderRequestController extends Controller
{
    /** @var PartnerOrderRequestRepositoryInterface $orderRequestRepo */
    private $orderRequestRepo;
    /** @var StatusChanger $statusChanger */
    private $statusChanger;

    public function __construct(PartnerOrderRequestRepositoryInterface $order_request_repo, StatusChanger $status_changer)
    {
        $this->orderRequestRepo = $order_request_repo;
        $this->statusChanger = $status_changer;
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
            $this->validate($request, ['filter' => 'required|string|in:all,new,missed']);
            $partner = $request->partner;
            $start_end_date = $time_frame->forTodayAndYesterday();
            $order_requests_formatted = [];

            list($offset, $limit) = calculatePagination($request);
            $order_requests = $this->orderRequestRepo->getAllByPartnerWithFilter($partner, $start_end_date, $request->filter, $offset, $limit);
            $order_requests->each(function ($order_requests) use (&$order_requests_formatted) {
                $manager = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource = new Item($order_requests, new OrderRequestTransformer());
                $order_requests_formatted[] = $manager->createData($resource)->toArray()['data'];
            });

            return api_response($request, null, 200, ['orders' => $order_requests_formatted]);
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

    public function accept($partner, $partner_order_request, Request $request)
    {
        try {
            $this->validate($request, [
                'resource_id' => 'required|int'
            ]);

            $this->statusChanger->setPartnerOrderRequest($request->partner_order_request)
                ->accept($request);
            if($this->statusChanger->hasError()) {
                return api_response($request, null, $this->statusChanger->getErrorCode(), [
                    'message' => $this->statusChanger->getErrorMessage()
                ]);
            }
            return api_response($request, null, 200);
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

    public function decline($partner, $partner_order_request, Request $request)
    {
        try {
            $this->validate($request, [
                'resource_id' => 'required|int'
            ]);

            $this->statusChanger->setPartnerOrderRequest($request->partner_order_request)
                ->decline($request);
            if($this->statusChanger->hasError()) {
                return api_response($request, null, $this->statusChanger->getErrorCode(), [
                    'message' => $this->statusChanger->getErrorMessage()
                ]);
            }
            return api_response($request, null, 200);
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
