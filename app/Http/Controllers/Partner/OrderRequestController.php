<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\OrderRequestTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Dal\PartnerOrderRequest\Statuses as PartnerOrderRequestStatuses;
use Sheba\Helpers\TimeFrame;
use Throwable;
use Illuminate\Validation\ValidationException;

class OrderRequestController extends Controller
{
    /** @var PartnerOrderRequestRepositoryInterface $orderRequestRepo */
    private $orderRequestRepo;

    public function __construct(PartnerOrderRequestRepositoryInterface $order_request_repo)
    {
        $this->orderRequestRepo = $order_request_repo;
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
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
