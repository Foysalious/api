<?php namespace App\Http\Controllers\Order;

use App\Exceptions\HyperLocationNotFoundException;
use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateFromBondhuRequest;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException;
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
     * @param Request $request
     * @param OrderPlace $order_place
     * @param OrderAdapter $order_adapter
     * @param UserAgentInformation $userAgentInformation
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     * @throws DestinationCitySameAsPickupException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ValidationException
     * @throws ServiceIsUnpublishedException
     */
    public function store(Request $request, OrderPlace $order_place, OrderAdapter $order_adapter, UserAgentInformation $userAgentInformation): JsonResponse
    {
        if ($this->isFraudOrder($request)) {
            Log::info("FRAUD_ORDER_PLACE: " . json_encode($request->all()) . " REQUEST_HEADER: " . json_encode($request->header()));
            return api_response($request, null, 500);
        }

        $request->merge(['mobile' => formatMobile(preg_replace('/\b \b|-/', '', $request->mobile))]);

        $this->validate($request, [
            'name' => 'required|string',
            'services' => 'required|string',
            'sales_channel' => 'required|string',
//            'remember_token' => 'required|string',
            'mobile' => 'required|string|mobile:bd',
            'email' => 'sometimes|email',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet,bondhu_balance',
            'address' => 'required_without:address_id',
            'address_id' => 'required_without:address|numeric',
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
        $this->setModifierFromRequest($request);
        $userAgentInformation->setRequest($request);
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
            ->setUserAgentInformation($userAgentInformation)
            ->create();

        if (!$order) return api_response($request, null, 500);

        $order = Order::find($order->id);
        $customer = $request->customer;
        if (!empty($customer->profile->name) && empty($customer->profile->gender)) dispatch(new AddCustomerGender($customer->profile));
        $payment_method = $request->payment_method;
        /** @var Payment $payment */
        $payment = $this->getPayment($payment_method, $order, $order_adapter);
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

        $this->sendSmsToCustomer($customer, $order);

        return api_response($request, null, 200, $order_with_response_data);
    }

    private function setModifierFromRequest(Request $request)
    {
        if ($request->has('created_by_type')) {
            if ($request->created_by_type === Resource::class) {
                $this->setModifier(Resource::find((int)$request->created_by));
                return;
            }
        }

        if ($request->has('created_by')) $this->setModifier(User::find((int)$request->created_by));
        else $this->setModifier($request->customer);
    }

    /**
     * @TODO FIx notification sending
     * @param $customer
     * @param $order
     */
    private function sendNotifications($customer, $order)
    {
        try {
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
        } catch (Throwable $e) {
            logError($e);
        }
    }

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

    /**
     * TEMPORARY - FRAUD CHECK
     *
     * @param Request $request
     * @return bool
     */
    private function isFraudOrder(Request $request): bool
    {
        $fraudulent_address_id = [
            305173, 305168, 305151, 305137, 305134, 305128, 305093, 305067, 305019, 305014, 304986, 304973, 304921, 304913, 304871, 304854, 304853, 304751, 304695, 304693, 304680, 304517, 304490, 304470, 304396, 304355, 304269, 304265, 304258, 304242, 304222, 304218, 304209, 304200, 304015, 303937, 303925, 303921, 303900, 303776, 303728, 303718, 303704, 303694, 303692, 303689, 303665, 303604, 303569, 303486, 303447, 303438, 303427, 303422, 303409, 303148, 302995, 302859, 302727, 302679, 302641, 302576, 302573, 302490, 302463, 302450, 302446, 302392, 302374, 302227, 302224, 302130, 301956, 301893, 301882, 301876, 301857, 301795, 301710, 301683, 301681, 301667, 301561, 301334, 301333, 301328, 301327, 301326, 301273, 301218, 301169, 301110, 301039, 300991, 300846, 300840, 300769, 300768, 300745, 300733, 300684, 300625, 300619, 300538, 300454, 300451, 300447, 300320, 300309, 300289, 300287, 300286, 300220, 300194, 300191, 300135, 300108, 299976, 299965, 299936, 299931, 299930, 299929, 299914, 299888, 299885, 299847, 299831, 299819, 299782, 299687, 299639, 299616, 299608, 299598, 299578, 299549, 299514, 299355, 299328, 299302, 299291, 299280, 299227, 299206, 299171, 299116, 299100, 298988, 298986, 298896, 298885, 298829, 298812, 298760, 298499, 298414, 298399, 298362, 298338, 298230, 298195, 298190, 298145, 298143, 298094, 298092, 298049, 297977, 297972, 297959, 297956, 297952, 297915, 297863, 297648, 297580, 297529, 297522, 297393, 297380, 297376, 297342, 297324, 297323, 297318, 297310, 297300, 297267, 297062, 297054, 296786, 296762, 296446, 296445, 296376, 296151, 296071, 296001, 295963, 295931, 295904, 295869, 295857, 295741, 295739, 295639, 295413, 295397, 295270, 294753, 294750, 294646, 294566, 294565, 294541, 294384, 294366, 294299, 294244, 294134, 294130, 294000, 293856, 293776, 293694, 293688, 293687, 293593, 293536, 293425, 293418, 293308, 293220, 293144, 293135, 293040, 293038, 293034, 292924, 292886, 292822, 292791, 292762, 292732, 292687
        ];
        if ($request->has('address') && $request->address == "Abdul Bariq Bhuaian Sharak, Korail, Dhaka")
            return true;
        if ($request->has('address_id') && in_array($request->address_id, $fraudulent_address_id))
            return true;

        return false;
    }
}
