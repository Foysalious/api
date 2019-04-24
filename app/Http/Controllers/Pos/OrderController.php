<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;

use App\Models\PosOrderItem;

use App\Transformers\PosOrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\ModificationFields;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\Pos\Jobs\OrderBillEmail;
use Sheba\Pos\Jobs\OrderBillSms;
use Sheba\Pos\Order\Creator;
use Sheba\Pos\Order\QuickCreator;
use Sheba\Pos\Order\RefundNatures\NatureFactory;
use Sheba\Pos\Order\RefundNatures\Natures;
use Sheba\Pos\Order\RefundNatures\RefundNature;
use Sheba\Pos\Order\RefundNatures\ReturnNatures;
use Sheba\Pos\Order\Updater;
use Throwable;

class OrderController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        try {
            $status = $request->status;
            $orders = PosOrder::with('items', 'customer')->orderBy('created_at', 'desc')->get();
            $final_orders = array();
            foreach ($orders as $index => $order) {
                $order_data = $order->calculate();
                $manager = new Manager();
                $manager->setSerializer(new ArraySerializer());
                $resource = new Item($order_data, new PosOrderTransformer());
                $order_formatted = $manager->createData($resource)->toArray();
                $order_create_date = Carbon::parse($order_formatted['created_at'])->format('Y-m-d');
                if (!isset($final_orders[$order_create_date])) $final_orders[$order_create_date] = array();
                if (!$status || ($status && $order_formatted['payment_status'] === $status)) array_push($final_orders[$order_create_date], $order_formatted);
            }
            $orders_formatted = array();
            foreach ($final_orders as $key => $value) {
                $order_list = array('date' => $key, 'orders' => $value);
                array_push($orders_formatted, $order_list);
            }
            return api_response($request, $orders_formatted, 200, ['orders' => $orders_formatted]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $order = PosOrder::with('items', 'customer.profile')->find($request->order)->calculate();
            if (!$order) return api_response($request, null, 404, ['msg' => 'Order Not Found']);

            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Item($order, new PosOrderTransformer());
            $order = $manager->createData($resource)->toArray();

            return api_response($request, null, 200, ['order' => $order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, ['services' => 'required|string', 'amount' => 'required|numeric', 'paid_amount' => 'required|numeric', 'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))]);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
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

    public function quickStore(Request $request, QuickCreator $creator)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric', 'paid_amount' => 'required|numeric', 'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))]);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
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
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update(Request $request, Updater $updater)
    {
        $this->setModifier($request->manager_resource);

        try {
            /** @var PosOrder $order */
            $order = PosOrder::with('items')->find($request->order);
            $is_returned = ($this->isReturned($order, $request));
            $refund_nature = $is_returned ? Natures::RETURNED : Natures::EXCHANGED;
            $return_nature = $is_returned ? $this->getReturnType($request) : null;

            /** @var RefundNature $refund */
            $refund = NatureFactory::getRefundNature($order, $request->all(), $refund_nature, $return_nature);
            $refund->update();

            return api_response($request, null, 200, ['msg' => 'Order Updated Successfully', 'order' => $order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param PosOrder $order
     * @param Request $request
     * @return bool
     */
    private function isReturned(PosOrder $order, Request $request)
    {
        $services = $order->items->pluck('service_id')->toArray();
        $request_services = collect(json_decode($request->services, true))->pluck('id')->toArray();

        return $services === $request_services;
    }

    public function getReturnType(Request $request)
    {
        $request_services_quantity = collect(json_decode($request->services, true))->pluck('quantity')->toArray();
        return (empty(array_filter($request_services_quantity))) ? ReturnNatures::FULL_RETURN : ReturnNatures::PARTIAL_RETURN;
    }

    /**
     * SMS TO CUSTOMER ABOUT POS ORDER BILLS
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSms(Request $request)
    {
        try {
            $this->setModifier($request->manager_resource);
            /** @var PosOrder $order */
            $order = PosOrder::with('items')->find($request->order)->calculate();

            if (!$order) return api_response($request, null, 404, ['msg' => 'Order not found']);
            if (!$order->customer->profile->mobile) return api_response($request, null, 404, ['msg' => 'Customer mobile not found']);

            dispatch(new OrderBillSms($order));
            return api_response($request, null, 200, ['msg' => 'SMS Send Successfully']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * EMAIL TO CUSTOMER ABOUT POS ORDER BILLS
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEmail(Request $request)
    {
        try {
            $this->setModifier($request->manager_resource);
            /** @var PosOrder $order */
            $order = PosOrder::with('items')->find($request->order)->calculate();

            if (!$order) return api_response($request, null, 404, ['msg' => 'Order not found']);
            if (!$order->customer->profile->email) return api_response($request, null, 404, ['msg' => 'Customer email not found']);

            dispatch(new OrderBillEmail($order));
            return api_response($request, null, 200, ['msg' => 'Email Send Successfully']);
        } catch (Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
