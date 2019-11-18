<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\OrderRequestTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\OrderRequest\Repositories\Interfaces\OrderRequestRepositoryInterface;
use Sheba\OrderRequest\Status;
use Throwable;
use Illuminate\Validation\ValidationException;

class OrderRequestController extends Controller
{
    /** @var OrderRequestRepositoryInterface $orderRequestRepo */
    private $orderRequestRepo;

    public function __construct(OrderRequestRepositoryInterface $order_request_repo)
    {
        $this->orderRequestRepo = $order_request_repo;
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function lists($partner, Request $request)
    {
        try {
            $this->validate($request, ['filter' => 'required|string|in:all,new,missed']);
            $partner = $request->partner;
            $order_requests_formatted = [];
            $start_date = Carbon::now()->subDay()->startOfDay()->format("y-m-d H:s:i");
            $end_date = Carbon::now()->endOfDay()->format("y-m-d H:s:i");
            $order_requests = $this->orderRequestRepo->load()
                ->where('partner_id', $partner->id)
                ->whereBetween('created_at', [$start_date, $end_date]);;

            if (Status::MISSED == $request->filter)
                $order_requests = $order_requests->status('missed');
            elseif ($request->filter == "new")
                $order_requests = $order_requests->openRequest();

            list($offset, $limit) = calculatePagination($request);
            $order_requests = $order_requests->orderBy('created_at', 'desc')->skip($offset)->limit($limit);
            $order_requests->get()->each(function ($order_requests) use (&$order_requests_formatted) {
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
}
