<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartnerOrder;
use App\Repositories\JobServiceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Checkout\Checkout;
use App\Sheba\Checkout\OnlinePayment;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Redis;
use DB;
use Sheba\OnlinePayment\Bkash;
use Sheba\OnlinePayment\Payment;
use Sheba\PayCharge\Adapters\OrderAdapter;
use Sheba\PayCharge\PayCharge;

class OrderController extends Controller
{
    private $orderRepository;
    private $jobServiceRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->jobServiceRepository = new JobServiceRepository();
    }

    public function checkOrderValidity(Request $request)
    {
        try {
            $key = Redis::get($request->s_token);
            if ($key != null) {
                Redis::del($request->s_token);
                return api_response($request, null, 200);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $this->validate($request, [
                'location' => 'required',
                'services' => 'required|string',
                'sales_channel' => 'required|string',
                'partner' => 'required',
                'remember_token' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'email' => 'sometimes|email',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'payment_method' => 'required|string|in:cod,online,bkash',
                'address' => 'required_without:address_id',
                'address_id' => 'required_without:address',
                'resource' => 'sometimes|numeric',
            ], ['mobile' => 'Invalid mobile number!']);
            $customer = $request->customer;
            $validation = new Validation($request);
            if (!$validation->isValid()) {
                $sentry = app('sentry');
                $sentry->user_context(['request' => $request->all(), 'message' => $validation->message]);
                $sentry->captureException(new \Exception($validation->message));
                return api_response($request, $validation->message, 400, ['message' => $validation->message]);
            }
            $order = new Checkout($customer);
            $order = $order->placeOrder($request);
            if ($order) {
                if ($order->voucher_id) $this->updateVouchers($order, $customer);
                $order_adapter = new OrderAdapter($order->partnerOrders[0], 1);
                $payment = $link = null;
                if ($request->payment_method !== 'cod') {
                    $payment = (new PayCharge($request->payment_method))->payCharge($order_adapter->getPayable());
                    $link = $payment['link'];
                }
                $this->sendNotifications($customer, $order);
                return api_response($request, $order, 200, ['link' => $link, 'job_id' => $order->jobs->first()->id, 'order_code' => $order->code(), 'payment' => $payment]);
            }
            return api_response($request, $order, 500);
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

    private function updateVouchers($order, Customer $customer)
    {
        try {
            if ($order->voucher_id != null) {
                $voucher = $order->voucher;
                $this->updateVoucherInPromoList($customer, $voucher, $order);
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sendNotifications($customer, $order)
    {
        try {
            $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
            $partner = $order->partnerOrders->first()->partner;
            if ((bool)env('SEND_ORDER_CREATE_SMS')) {
                (new SmsHandler('order-created'))->send($customer->profile->mobile, [
                    'order_code' => $order->code()
                ]);
                if (!$order->jobs->first()->resource_id) {
                    (new SmsHandler('order-created-to-partner'))->send($partner->getContactNumber(), [
                        'order_code' => $order->code(), 'partner_name' => $partner->name
                    ]);
                }
            }
            (new NotificationRepository())->send($order);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function updateVoucherInPromoList(Customer $customer, $voucher, $order)
    {
        $rules = json_decode($voucher->rules);
        if (array_key_exists('nth_orders', $rules) && !array_key_exists('ignore_nth_orders_if_used', $rules)) {
            $nth_orders = $rules->nth_orders;
            if ($customer->orders->count() == max($nth_orders)) {
                $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
                return;
            }
        }
        if ($voucher->usage($customer->id) == $voucher->max_order) {
            $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
            return;
        }
    }

    public function clearPayment(Request $request)
    {
        try {
            $redis_key_name = 'portwallet-payment-' . $request->invoice;
            $redis_key = Redis::get($redis_key_name);
            if ($redis_key) {
                $data = json_decode($redis_key);
                $response = (new OnlinePayment())->pay($data, $request);
                if ($response != null) {
                    Redis::set('portwallet-payment-app-' . $request->invoice, json_encode(['amount' => $data->amount,
                        'partner_order_id' => $data->partner_order_id, 'success' => $response['success'], 'isDue' => $response['isDue'],
                        'message' => $response['message']]));
                    Redis::expire('portwallet-payment-app' . $request->invoice, 3600);
                    Redis::del($redis_key_name);
                    if ($response['success']) {
                        return redirect($response['redirect_link']);
                    }
                }
            }
            return redirect(env('SHEBA_FRONT_END_URL'));
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function checkInvoiceValidity($customer, Request $request)
    {
        try {
            $redis_key_name = 'portwallet-payment-app-' . $request->invoice;
            $redis_key = Redis::get($redis_key_name);
            if ($redis_key != null) {
                $data = json_decode($redis_key);
                $partnerOrder = PartnerOrder::find((int)$data->partner_order_id);
                if ($partnerOrder->order->customer_id == $customer) {
                    return api_response($request, 1, 200, ['message' => $data->message]);
                }
            }
            return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}