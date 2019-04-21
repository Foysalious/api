<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;

use App\Transformers\PosOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\ModificationFields;
use Sheba\Pos\Order\Creator;
use Sheba\Pos\Order\QuickCreator;

class OrderController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        try {
            $status = $request->status;
            $orders = PosOrder::with('items')->orderBy('created_at','desc')->get();
            $final_orders = array();
            foreach ($orders as $index => $order) {
                $order_data = $order->calculate();
                $manager = new Manager();
                $manager->setSerializer(new ArraySerializer());
                $resource = new Item($order_data, new PosOrderTransformer());
                $order_formatted = $manager->createData($resource)->toArray();
                $order_create_date  = Carbon::parse($order_formatted['created_at'])->format('Y-m-d');
                if(!isset($final_orders[$order_create_date]))
                    $final_orders[$order_create_date] = array();
                if(!$status || ($status && $order_formatted['payment_status'] === $status) )
                    array_push($final_orders[$order_create_date], $order_formatted);
            }
            return api_response($request, $final_orders, 200, ['orders' => $final_orders]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $order = PosOrder::with('items')->find($request->order)->calculate();
            if (!$order) return api_response($request, null, 404, ['msg' => 'Order Not Found']);

            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Item($order, new PosOrderTransformer());
            $order = $manager->createData($resource)->toArray();

            return api_response($request, null, 200, ['order' => $order]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'amount' => 'required|numeric',
                'paid_amount' => 'required|numeric',
                'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))
            ]);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function quickStore(Request $request, QuickCreator $creator)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                'paid_amount' => 'required|numeric',
                'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))
            ]);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfItem()
    {
        return ['service_name', 'app_thumb', 'app_banner', 'price', 'stock'];
    }
}
