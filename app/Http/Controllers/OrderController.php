<?php namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Requests\BondhuOrderRequest;
use App\Jobs\AddCustomerGender;
use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Payment;
use App\Repositories\JobServiceRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Bondhu\BondhuAutoOrder;
use App\Sheba\Checkout\Checkout;
use App\Sheba\Checkout\OnlinePayment;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redis;
use Sheba\OrderPlace\OrderPlace;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;
use Sheba\Portals\Portals;
use Sheba\Sms\Sms;
use Throwable;

class OrderController extends Controller
{
    use DispatchesJobs;

    private $orderRepository;
    private $jobServiceRepository;
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->orderRepository = new OrderRepository();
        $this->jobServiceRepository = new JobServiceRepository();
        $this->sms = $sms;
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
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request, OrderAdapter $order_adapter)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'services' => 'required|string',
                'sales_channel' => 'required|string',
                'partner' => 'required',
                'remember_token' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'email' => 'sometimes|email',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet',
                'address' => 'required_without:address_id',
                'address_id' => 'required_without:address',
                'resource' => 'sometimes|numeric',
                'is_on_premise' => 'sometimes|numeric',
                'partner_id' => 'sometimes|required|numeric',
                'emi_month' => 'numeric'
            ], ['mobile' => 'Invalid mobile number!']);
            $customer = $request->customer;
            $validation = new Validation($request);
            if (!$validation->isValid()) {
                $sentry = app('sentry');
                $sentry->user_context(['request' => $request->all(), 'message' => $validation->message]);
                $sentry->captureException(new Exception($validation->message));
                return api_response($request, $validation->message, 400, ['message' => $validation->message]);
            }
            $order = new Checkout($customer);
            $order = $order->placeOrder($request);
            if ($order) {
                if (!empty($customer->profile->name) && empty($customer->profile->gender)) dispatch(new AddCustomerGender($customer->profile));
                if ($order->voucher_id) $this->updateVouchers($order, $customer);
                $payment = $link = null;
                if ($request->payment_method !== 'cod') {
                    /** @var Payment $payment */
                    $payment = $this->getPayment($request->payment_method, $order, $order_adapter);
                    if ($payment) {
                        $link = $payment->redirect_url;
                        $payment = $payment->getFormattedPayment();
                    }
                }
                $this->sendNotifications($customer, $order);
                $partner = $order->partnerOrders()->first()->partner;
                return api_response($request, $order, 200, ['link' => $link, 'job_id' => $order->jobs->first()->id, 'provider_mobile' => $partner->getContactNumber(),
                    'order_code' => $order->code(), 'payment' => $payment]);
            }
            return api_response($request, $order, 500);
        } catch (HyperLocationNotFoundException $e) {
            return api_response($request, null, 400, ['message' => "You're out of service area"]);
        }
    }

    public function placeOrderFromBondhu(BondhuOrderRequest $request, $affiliate, BondhuAutoOrder $bondhu_auto_order,
                                         OrderPlace $order_place, OrderAdapter $order_adapter)
    {
        try {
            if (Affiliate::find($affiliate)->is_suspended) {
                return api_response($request, null, 400, ['message' => 'You\'re suspended can not place order now']);
            }

            if ($bondhu_auto_order->setServiceCategoryName()) {
                $payment = $link = null;
                DB::beginTransaction();
                $order = $bondhu_auto_order->place();
                if ($order) {
                    if ($order->voucher_id) $this->updateVouchers($order, $bondhu_auto_order->customer);
                    if ($request->payment_method !== 'cod') {
                        /** @var Payment $payment */
                        $payment = $this->getPayment($request->payment_method, $order, $order_adapter);
                        if ($payment) {
                            $link = $payment->redirect_url;
                            $payment = $payment->getFormattedPayment();
                        }
                    }

                    $this->sendNotifications($bondhu_auto_order->customer, $order);
                    if ($bondhu_auto_order->isAsOfflineBondhu()) {
                        $this->sendSms($affiliate, $order);
                    }
                    DB::commit();
                    return api_response($request, $order, 200, ['link' => $link, 'job_id' => $order->jobs->first()->id, 'order_code' => $order->code(), 'payment' => $payment]);
                } else {
                    DB::rollback();
                    return api_response($request, null, 400, ['message' => 'Order can not be created']);
                }
            } else {
                return api_response($request, null, 400, ['message' => 'Service is invalid']);
            }
        } catch (HyperLocationNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'You\'re out of service area']);
        } catch (QueryException $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (Throwable $exception) {
            DB::rollback();
            app('sentry')->captureException($exception);
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
        } catch (Throwable $e) {
            return null;
        }
    }

    private function sendSms($affiliate, $order)
    {
        $affiliate = Affiliate::find($affiliate);
        $agent_mobile = $affiliate->profile->mobile;
        $partner = $order->partnerOrders->first()->partner;
        $job = $order->lastJob();

        (new SmsHandler('order-created-to-bondhu'))->send($agent_mobile, [
            'service_name' => $job->category->name,
            'order_code' => $order->code(),
            'partner_name' => $partner->name,
            'partner_number' => $partner->getContactNumber(),
            'preferred_time' => $job->preferred_time,
            'preferred_date' => $job->schedule_date,
        ]);
    }

    private function sendNotifications($customer, $order)
    {
        try {
            $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
            /** @var Partner $partner */
            $partner = $order->partnerOrders->first()->partner;
            if ((bool)config('sheba.send_order_create_sms')) {
                if ($this->isSendingServedConfirmationSms($order)) {
                    (new SmsHandler('order-created'))->send($customer->profile->mobile, [
                        'order_code' => $order->code()
                    ]);
                }

                if (!$order->jobs->first()->resource_id) {
                    (new SmsHandler('order-created-to-partner'))->send($partner->getContactNumber(), [
                        'order_code' => $order->code(), 'partner_name' => $partner->name
                    ]);
                }
            }
            (new NotificationRepository())->send($order);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
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
        if ($voucher->usage($customer->profile) == $voucher->max_order) {
            $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
            return;
        }
    }

    private function getPayment($payment_method, Order $order, OrderAdapter $order_adapter)
    {
        $order_adapter->setPartnerOrder($order->partnerOrders[0])->setIsAdvancedPayment(1)->setEmiMonth(\request()->emi_month)->setPaymentMethod($payment_method);
        $payment = new ShebaPayment();
        $payment = $payment->setMethod($payment_method)->init($order_adapter->getPayable());
        return $payment->isInitiated() ? $payment : null;
    }

    /**
     * @param $order
     * @return bool
     */
    private function isSendingServedConfirmationSms($order)
    {
        return (
            !in_array($order->portal_name, config('sheba.stopped_sms_portal_for_customer')) &&
            !($order->portal_name == 'admin-portal' && $order->sales_channel == 'Bondhu')
        );
    }
}
