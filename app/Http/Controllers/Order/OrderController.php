<?php namespace App\Http\Controllers\Order;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Presenters\OrderPlacedResponse;
use App\Http\Requests\OrderCreateFromBondhuRequest;
use App\Http\Requests\OrderPlaceRequest;
use App\Jobs\AddCustomerGender;
use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Resource;
use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Bondhu\BondhuAutoOrderV3;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\OrderPlace\OrderPlace;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Portals\Portals;
use Sheba\UserAgentInformation;
use Throwable;

class OrderController extends Controller
{
    use DispatchesJobs;
    use ModificationFields;

    /**
     * @param OrderPlaceRequest $request
     * @param OrderAdapter $order_adapter
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     * @throws ValidationException
     * @throws \App\Exceptions\RentACar\DestinationCitySameAsPickupException
     * @throws \App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException
     * @throws \App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException
     * @throws \Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException
     * @throws \Exception
     */
    public function store(OrderPlaceRequest $request, OrderAdapter $order_adapter)
    {
        $this->setModifierFromRequest($request);
        $order = $request->buildOrderPlace()->create();
        if (!$order) return api_response($request, null, 500);

        $order = Order::find($order->id);
        $customer = $request->getCustomer();
        if (!empty($customer->profile->name) && empty($customer->profile->gender)) dispatch(new AddCustomerGender($customer->profile));
        $payment_method = $request->payment_method;
        /** @var Payment $payment */
        $payment = $this->getPayment($payment_method, $order, $order_adapter);
        if ($payment) $payment = $payment->getFormattedPayment();

        try {
            $this->sendNotifications($customer, $order);
            $this->sendSmsToCustomer($customer, $order);
        } catch (\Exception $e) {
            logError($e);
        }

        return api_response($request, null, 200, (new OrderPlacedResponse($order, $payment))->toArray());
    }

    private function setModifierFromRequest(OrderPlaceRequest $request)
    {
        $modifier = null;
        if ($request->has('created_by_type') && $request->created_by_type === Resource::class) {
            $modifier = Resource::find((int)$request->created_by);
        } else if ($request->has('created_by')) {
            $modifier = User::find((int)$request->created_by);
        } else {
            $modifier = $request->getCustomer();
        }
        $this->setModifier($modifier);
    }

    /**
     * @TODO FIx notification sending
     * @param $customer
     * @param $order
     * @throws \Exception
     */
    private function sendNotifications($customer, $order)
    {
        if (!$order->partnerOrders->first()->partner_id) return;

        /** @var Partner $partner */
        $partner = $order->partnerOrders->first()->partner;
        (new NotificationRepository())->send($order);
        if (!(bool)config('sheba.send_order_create_sms')) return;

        if (!$order->jobs->first()->resource_id) {
            (new SmsHandler('order-created-to-partner'))
                ->setBusinessType(BusinessType::SMANAGER)
                ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
                ->send($partner->getContactNumber(), [
                'order_code' => $order->code(), 'partner_name' => $partner->name
            ]);
        }
    }

    /**
     * @param $customer
     * @param $order
     * @throws \Exception
     */
    private function sendSmsToCustomer($customer, $order)
    {
        $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
        if (!$this->isSendingServedConfirmationSms($order)) return;

        (new SmsHandler('order-created'))
            ->setBusinessType(BusinessType::MARKETPLACE)
            ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
            ->send($customer->profile->mobile, [
                'order_code' => $order->code()
            ]);
    }

    public function storeFromBondhu(OrderCreateFromBondhuRequest $request, $affiliate, BondhuAutoOrderV3 $bondhu_auto_order, OrderPlace $order_place, OrderAdapter $order_adapter)
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
                        $payment = $this->getPayment($request->payment_method, $order, $order_adapter);
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
            logError($e);
            return api_response($request, null, 500);
        } catch (Throwable $e) {
            DB::rollback();
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function updateVouchers($order, Customer $customer)
    {
        try {
            if ($order->voucher_id == null) return;

            $voucher = $order->voucher;
            $this->updateVoucherInPromoList($customer, $voucher, $order);
        } catch (Throwable $e) {
            logError($e);
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
        }
    }

    /**
     * @param $payment_method
     * @param Order $order
     * @param OrderAdapter $order_adapter
     * @return Payment|null
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    private function getPayment($payment_method, Order $order, OrderAdapter $order_adapter)
    {
        if ($payment_method == 'cod') return null;
        $payable = $order_adapter->setPartnerOrder($order->partnerOrders[0])
            ->setIsAdvancedPayment(1)->setEmiMonth(\request()->emi_month)
            ->setPaymentMethod($payment_method)
            ->getPayable();
        $payment = (new PaymentManager())->setMethodName($payment_method)->setPayable($payable)->init();
        return $payment->isInitiated() ? $payment : null;
    }

    private function sendNotificationsForBondhu($customer, $order)
    {
        try {
            $customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
            if ((bool)config('sheba.send_order_create_sms')) {
                if ($this->isSendingServedConfirmationSms($order)) {
                    (new SmsHandler('order-created'))
                        ->setBusinessType(BusinessType::MARKETPLACE)
                        ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
                        ->send($customer->profile->mobile, [
                            'order_code' => $order->code()
                        ]);
                }
            }
        } catch (Throwable $e) {
            logError($e);
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
            !($order->portal_name == Portals::ADMIN && $order->sales_channel == 'Bondhu')
        );
    }

    private function sendSms($affiliate, $order)
    {
        $affiliate = Affiliate::find($affiliate);
        $agent_mobile = $affiliate->profile->mobile;
        $job = $order->lastJob();

        (new SmsHandler('order-created-to-bondhu'))
            ->setBusinessType(BusinessType::BONDHU)
            ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
            ->send($agent_mobile, [
                'service_name' => $job->category->name,
                'order_code' => $order->code(),
                'preferred_time' => $job->preferred_time,
                'preferred_date' => $job->schedule_date,
            ]);
    }
}
