<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;

use App\Models\PosOrder;

use App\Transformers\CustomSerializer;
use App\Transformers\PosOrderTransformer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;

use Sheba\ModificationFields;

use Sheba\Pos\Jobs\OrderBillEmail;
use Sheba\Pos\Jobs\OrderBillSms;
use Sheba\Pos\Order\Creator;
use Sheba\Pos\Payment\Creator as PaymentCreator;
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
        ini_set('memory_limit', '2096M');
        try {
            $status = $request->status;
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);

            /** @var PosOrder $orders */
            $orders = PosOrder::with('items.service.discounts', 'customer', 'payments', 'logs', 'partner')
                ->byPartner($partner->id)
                ->orderBy('created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $final_orders = [];

            foreach ($orders as $index => $order) {
                $order->isRefundable();
                $order_data = $order->calculate();
                $manager = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource = new Item($order_data, new PosOrderTransformer());
                $order_formatted = $manager->createData($resource)->toArray()['data'];

                $order_create_date = $order->created_at->format('Y-m-d');

                if (!isset($final_orders[$order_create_date])) $final_orders[$order_create_date] = [];
                if (($status == "null") || !$status || ($status && $order->getPaymentStatus() == $status)) {
                    array_push($final_orders[$order_create_date], $order_formatted);
                }
            }

            $orders_formatted = [];
            foreach ($final_orders as $key => $value) {
                if (count($value) > 0) {
                    $order_list = ['date' => $key, 'orders' => $value];
                    array_push($orders_formatted, $order_list);
                }
            }

            return api_response($request, $orders_formatted, 200, ['orders' => $orders_formatted]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        try {
            /** @var PosOrder $order */
            $order = PosOrder::with('items.service.discounts', 'customer', 'payments', 'logs', 'partner')->find($request->order)->calculate();
            if (!$order) return api_response($request, null, 404, ['msg' => 'Order Not Found']);

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($order, new PosOrderTransformer());
            $order = $manager->createData($resource)->toArray();

            return api_response($request, null, 200, ['order' => $order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return array
     */
    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'paid_amount' => 'sometimes|required|numeric',
                'payment_method' => 'sometimes|required|string|in:' . implode(',', config('pos.payment_method'))
            ]);
            $this->setModifier($request->manager_resource);

            $creator->setData($request->all());
            if ($error = $creator->hasError()) return $error;
            $order = $creator->create();

            $order = $order->calculate();
            $this->sendCustomerSms($order);
            $order->payment_status = $order->getPaymentStatus();
            $order->client_pos_order_id = $request->client_pos_order_id;
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

    private function sendCustomerSms(PosOrder $order)
    {
        if ($order->customer && $order->customer->profile->mobile)
            dispatch(new OrderBillSms($order));
    }

    /**
     * @param Request $request
     * @param QuickCreator $creator
     * @return JsonResponse
     */
    public function quickStore(Request $request, QuickCreator $creator)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric',
                /*'paid_amount' => 'required|numeric',
                'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))*/
            ]);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();
            $order = $order->calculate();
            $this->sendCustomerSms($order);
            $order->payment_status = $order->getPaymentStatus();

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
            $return_nature = $is_returned ? $this->getReturnType($request, $order) : null;

            /** @var RefundNature $refund */
            $refund = NatureFactory::getRefundNature($order, $request->all(), $refund_nature, $return_nature);
            $refund->update();

            $order->payment_status = $order->calculate()->getPaymentStatus();
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

    private function getReturnType(Request $request, PosOrder $order)
    {
        $request_services_quantity = collect(json_decode($request->services, true))->pluck('quantity')->toArray();

        $is_full_order_returned = (empty(array_filter($request_services_quantity)));
        $is_item_added = array_sum($request_services_quantity) > $order->items->sum('quantity');

        return $is_full_order_returned ?
            ReturnNatures::FULL_RETURN :
            ($is_item_added ? ReturnNatures::QUANTITY_INCREASE : ReturnNatures::PARTIAL_RETURN);
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
            if (!$order->customer) return api_response($request, null, 404, ['msg' => 'Customer not found']);
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
            if (!$order->customer) return api_response($request, null, 404, ['msg' => 'Customer not found']);
            if (!$order->customer->profile->email) return api_response($request, null, 404, ['msg' => 'Customer email not found']);

            dispatch(new OrderBillEmail($order));
            return api_response($request, null, 200, ['msg' => 'Email Send Successfully']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function collectPayment(Request $request, PaymentCreator $payment_creator)
    {
        $this->setModifier($request->manager_resource);
        try {
            $this->validate($request, [
                'paid_amount' => 'required|numeric',
                'payment_method' => 'required|string|in:' . implode(',', config('pos.payment_method'))
            ]);

            /** @var PosOrder $order */
            $order = PosOrder::find($request->order);
            $payment_data = [
                'pos_order_id' => $order->id,
                'amount' => $request->paid_amount,
                'method' => $request->payment_method
            ];
            $payment_creator->credit($payment_data);
            $order = $order->calculate();
            $order->payment_status = $order->getPaymentStatus();

            return api_response($request, null, 200, ['msg' => 'Payment Collect Successfully', 'order' => $order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
