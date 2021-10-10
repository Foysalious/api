<?php namespace App\Http\Controllers\PosOrder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\VoucherController;
use App\Sheba\PosOrderService\Services\OrderService;
use Illuminate\Http\Request;
use Sheba\DueTracker\Exceptions\UnauthorizedRequestFromExpenseTrackerException;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\Pos\Order\PosOrderTypes;
use Sheba\PosOrderService\Services\PaymentService;

class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var PaymentService
     */
    private $paymentService;


    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $hasQueryStr = strpos($request->getRequestUri(), '?');
        $queryStr = $hasQueryStr ? '?' . substr($request->getRequestUri(), strpos($request->getRequestUri(), "?") + 1) : '';
        $order = $this->orderService->setPartnerId($partner->id)->setFilterParams($queryStr)->getOrderList();
        if (!$order) return http_response($request, "অর্ডারটি পাওয়া যায় নি", 404, $order);
        else return http_response($request, null, 200, $order);
    }

    public function show(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $orderDetails = $this->orderService->setPartnerId($partner->id)->setOrderId($order_id)->getDetails();
        if (!$orderDetails) return http_response($request, "অর্ডারটি পাওয়া যায় নি", 404, $orderDetails);
        else return http_response($request, null, 200, $orderDetails);
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService
            ->setToken(bearerToken($request))
            ->setPartnerId($partner->id)
            ->setCustomerId($request->customer_id)
            ->setDeliveryAddress($request->delivery_address)
            ->setSalesChannelId($request->sales_channel_id)
            ->setDeliveryCharge($request->delivery_charge)
            ->setDeliveryMobile($request->delivery_mobile)
            ->setDeliveryName($request->delivery_name)
            ->setStatus($request->status)
            ->setSkus($request->skus)
            ->setDiscount($request->discount)
            ->setPaymentMethod($request->payment_method)
            ->setPaymentLinkAmount($request->payment_link_amount)
            ->setPaidAmount($request->paid_amount)
            ->setVoucherId($request->voucher_id)
            ->setEmiMonth($request->emi_month)
            ->store();
        return http_response($request, null, 200, $response);

    }

    public function updateStatus(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService
            ->setPartnerId($partner->id)
            ->setOrderId($request->order)
            ->setStatus($request->status)
            ->updateStatus();
        return http_response($request, null, 200, $response);
    }

    public function update(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService
            ->setPartnerId($partner->id)
            ->setOrderId($order_id)
            ->setSalesChannelId($request->sales_channel_id)
            ->setSkus($request->skus)
            ->setEmiMonth($request->emi_month)
            ->setInterest($request->interest)
            ->setDeliveryCharge($request->delivery_charge)
            ->setBankTransactionCharge($request->bank_transaction_charge)
            ->setDeliveryName($request->delivery_name)
            ->setDeliveryMobile($request->delivery_mobile)
            ->setDeliveryAddress($request->delivery_address)
            ->setNote($request->note)
            ->setVoucherId($request->voucher_id)
            ->setDiscount($request->discount)
            ->setPaymentMethod($request->payment_method)
            ->setPaidAmount($request->paid_amount)
            ->setToken($request->header('Authorization'))
            ->update();
        return http_response($request, null, 200, $response);
    }

    public function updateCustomer(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService
            ->setPartnerId($partner->id)
            ->setOrderId($order_id)
            ->setCustomerId($request->customer_id)
            ->updateCustomer();
        return http_response($request, null, 200, $response);
    }

    public function destroy(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService->setPartnerId($partner->id)->setOrderId($order_id)->delete();
        return http_response($request, null, 200, $response);
    }

    public function validatePromo(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string',
        ]);
        /** @var VoucherController $promoValidator */
        $promoValidator = app(VoucherController::class);
        return $promoValidator->validateVoucher($request);
    }

    public function logs(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $orderLogs = $this->orderService->setPartnerId($partner->id)->setOrderId($order_id)->getLogs();
        if (!$orderLogs) return http_response($request, "অর্ডারটি পাওয়া যায় নি", 404, $orderLogs);
        else return http_response($request, null, 200, $orderLogs);
    }

    /**
     * @throws UnauthorizedRequestFromExpenseTrackerException
     */
    public function onlinePayment($order, Request $request)
    {
        $this->validate($request, [
            'pos_order_type' =>'required|in:' . implode(',', PosOrderTypes::get()),
            'partner_id' => 'required|int',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'emi_month' => 'required|int',
            'interest' => 'required|numeric',
            'is_paid_by_customer' => 'required|boolean',
            'api_key' => 'required',
        ]);
        if($request->api_key != config('expense_tracker.api_key'))
            throw new UnauthorizedRequestFromExpenseTrackerException("Unauthorized Request");
        $this->paymentService->setPosOrderId($order)->setPosOrderType($request->pos_order_type)->setPartnerId($request->partner_id)->setAmount($request->amount)
            ->setMethod($request->payment_method)->setEmiMonth($request->emi_month)->setInterest($request->interest)
            ->onlinePayment();
        return http_response($request, null, 200);
    }

    public function paymentLinkCreated($order, Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'purpose' => 'required',
            'customer_id' => 'sometimes',
            'emi_month' => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
            'interest_paid_by' => 'sometimes|in:' . implode(',', PaymentLinkStatics::paidByTypes()),
            'transaction_charge' => 'sometimes|numeric|min:' . PaymentLinkStatics::get_payment_link_commission(),
            'pos_order_id' => 'required'
        ]);
        //TODO: Order Payment Link Created Event
        return http_response($request, null, 200);
    }

}