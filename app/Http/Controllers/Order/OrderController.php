<?php namespace App\Http\Controllers\Order;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateFromBondhuRequest;
use App\Jobs\AddCustomerGender;
use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Bondhu\BondhuAutoOrderV3;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\OrderPlace\OrderPlace;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;
use Throwable;

class OrderController extends Controller
{
    use DispatchesJobs;
    use ModificationFields;

    /**
     * @param Request $request
     * @param OrderPlace $order_place
     * @return JsonResponse
     */
    public function store(Request $request, OrderPlace $order_place)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $this->validate($request, [
                'name' => 'required|string',
                'services' => 'required|string',
                'sales_channel' => 'required|string',
                'remember_token' => 'required|string',
                'mobile' => 'required|string|mobile:bd',
                'email' => 'sometimes|email',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet',
                'address' => 'required_without:address_id',
                'address_id' => 'required_without:address',
                'partner' => 'sometimes|required',
                'partner_id' => 'sometimes|required|numeric',
                'affiliate_id' => 'sometimes|required|numeric',
                'info_call_id' => 'sometimes|required|numeric',
                'affiliation_id' => 'sometimes|required|numeric',
                'vendor_id' => 'sometimes|required|numeric',
                'crm_id' => 'sometimes|required|numeric',
                'business_id' => 'sometimes|required|numeric',
                'voucher' => 'sometimes|required|numeric',
                'emi_month' => 'numeric',
                'created_by' => 'numeric',
                'created_by_name' => 'string',
            ], ['mobile' => 'Invalid mobile number!']);
            if ($request->has('created_by')) $this->setModifier(User::find((int)$request->created_by));
            else $this->setModifier($request->customer);
            $order = $order_place
                ->setCustomer($request->customer)
                ->setDeliveryName($request->name)
                ->setDeliveryAddressId($request->address_id)
                ->setDeliveryAddress($request->address)
                ->setPaymentMethod($request->payment_method)
                ->setDeliveryMobile($request->mobile)
                ->setSalesChannel($request->sales_channel)
                ->setPartnerId($request->partner_id)
                ->setSelectedPartnerId($request->partner)
                ->setAdditionalInformation($request->additional_information)
                ->setAffiliationId($request->affiliation_id)
                ->setInfoCallId($request->info_call_id)
                ->setBusinessId($request->business_id)
                ->setCrmId($request->crm_id)
                ->setVoucherId($request->voucher)
                ->setServices($request->services)
                ->setScheduleDate($request->date)
                ->setScheduleTime($request->time)
                ->setVendorId($request->vendor_id)
                ->create();
            if (!$order) return api_response($request, null, 500);
            $order = Order::find($order->id);
            $customer = $request->customer;
            if (!empty($customer->profile->name) && empty($customer->profile->gender)) dispatch(new AddCustomerGender($customer->profile));
            $payment_method = $request->payment_method;
            /** @var Payment $payment */
            $payment = $this->getPayment($payment_method, $order);
            if ($payment) $payment = $payment->getFormattedPayment();
            $job = $order->jobs->first();
            $partner_order = $job->partnerOrder;
            $order_with_response_data = [
                'job_id' => $job->id,
                'order_code' => $order->code(),
                'payment' => $payment,
                'order' => [
                    'id' => $order->id,
                    'code' => $order->code(),
                    'job' => ['id' => $job->id]
                ]
            ];
            if ($partner_order->partner_id) {
                $order_with_response_data['provider_mobile'] = $partner_order->partner->getContactNumber();
                $this->sendNotifications($customer, $order);
            }

            return api_response($request, null, 200, $order_with_response_data);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @TODO FIx notification sending
     * @param $customer
     * @param $order
     * @return |null
     */
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

    /**
     * @param OrderCreateFromBondhuRequest $request
     * @param $affiliate
     * @param BondhuAutoOrderV3 $bondhu_auto_order
     * @param OrderPlace $order_place
     * @return JsonResponse
     */
    public function storeFromBondhu(OrderCreateFromBondhuRequest $request, $affiliate, BondhuAutoOrderV3 $bondhu_auto_order, OrderPlace $order_place)
    {
        try {
            if (Affiliate::find($affiliate)->is_suspended)
                return api_response($request, null, 400, ['message' => 'You\'re suspended can not place order now']);

            if ($bondhu_auto_order->setServiceCategoryName()) {
                $payment = $link = null;
                DB::beginTransaction();
                $order = $bondhu_auto_order->placeV3($request, $order_place);
                if ($order) {
                    if ($order->voucher_id) $this->updateVouchers($order, $bondhu_auto_order->customer);
                    if ($request->payment_method !== 'cod') {
                        /** @var Payment $payment */
                        $payment = $this->getPayment($request->payment_method, $order);
                        if ($payment) {
                            $link = $payment->redirect_url;
                            $payment = $payment->getFormattedPayment();
                        }
                    }

                    $this->sendNotificationsForBondhu($bondhu_auto_order->customer, $order);
                    if ($bondhu_auto_order->isAsOfflineBondhu()) $this->sendSms($affiliate, $order);
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

    private function getPayment($payment_method, Order $order)
    {
        try {
            if ($payment_method == 'cod') return null;
            $order_adapter = new OrderAdapter($order->partnerOrders[0], 1);
            $order_adapter->setEmiMonth(\request()->emi_month);
            $order_adapter->setPaymentMethod($payment_method);
            $payment = new ShebaPayment();
            $payment = $payment->setMethod($payment_method)->init($order_adapter->getPayable());
            return $payment->isInitiated() ? $payment : null;
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

    private function sendNotificationsForBondhu($customer, $order)
    {
        try {
            $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
            if ((bool)config('sheba.send_order_create_sms')) {
                if ($this->isSendingServedConfirmationSms($order)) {
                    (new SmsHandler('order-created'))->send($customer->profile->mobile, [
                        'order_code' => $order->code()
                    ]);
                }
            }
            // (new NotificationRepository())->send($order);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
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

    private function sendSms($affiliate, $order)
    {
        $affiliate = Affiliate::find($affiliate);
        $agent_mobile = $affiliate->profile->mobile;
        $job = $order->lastJob();

        (new SmsHandler('order-created-to-bondhu'))->send($agent_mobile, [
            'service_name' => $job->category->name,
            'order_code' => $order->code(),
            'preferred_time' => $job->preferred_time,
            'preferred_date' => $job->schedule_date,
        ]);
    }
}
