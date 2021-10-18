<?php namespace App\Http\Controllers\PosOrder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentLink\PaymentLinkController;
use App\Http\Controllers\VoucherController;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\PosOrderService\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\DueTracker\Exceptions\UnauthorizedRequestFromExpenseTrackerException;
use Sheba\EMI\Calculations;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\Pos\Order\PosOrderResolver;
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
    public function onlinePayment($partner, $order, Request $request): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
            'payment_method_en' => 'sometimes',
            'payment_method_bn' => 'sometimes',
            'payment_method_icon' => 'sometimes',
            'emi_month' => 'sometimes',
            'interest' => 'sometimes',
            'is_paid_by_customer' => 'sometimes',
        ]);
        if ($request->header('api-key') != config('expense_tracker.api_key'))
            throw new UnauthorizedRequestFromExpenseTrackerException("Unauthorized Request");

        $method_details = ['payment_method_bn' => $request->payment_method_bn, 'payment_method_icon' => $request->payment_method_icon];
        $posOrder = PosOrder::find($order);
        $pos_order_type = $posOrder && !$posOrder->is_migrated ? PosOrderTypes::OLD_SYSTEM : PosOrderTypes::NEW_SYSTEM;
        $this->paymentService->setPosOrderId($order)->setPosOrderType($pos_order_type)->setPartnerId($partner)->setAmount($request->amount)
            ->setMethod($request->payment_method_en)->setMethodDetails($method_details)->setEmiMonth($request->emi_month)->setInterest($request->interest)
            ->onlinePayment();
        return http_response($request, null, 200);
    }

    /**
     * @throws UnauthorizedRequestFromExpenseTrackerException
     */
    public function paymentLinkCreated($partner, $order, Request $request): JsonResponse
    {
        $this->validate($request, [
            'link_id' => 'sometimes',
            'reason' => 'sometimes',
            'status' => 'sometimes',
            'link' => 'sometimes',
            'emi_month' => 'sometimes',
            'interest' => 'sometimes',
            'bank_transaction_charge' => 'sometimes',
            'paid_by' => 'sometimes|in:' . implode(',', PaymentLinkStatics::paidByTypes()),
            'partner_profit' => 'sometimes'
        ]);
        if ($request->header('api-key') != config('expense_tracker.api_key'))
            throw new UnauthorizedRequestFromExpenseTrackerException("Unauthorized Request");
        $partner = Partner::find($partner);
        $interest = 0;
        $bank_transaction_charge = 0;
        if ($request->paid_by == PaymentLinkStatics::paidByTypes()[1]) {
            $interest = $request->interest;
            $bank_transaction_charge = $request->bank_transaction_charge;
        }
        if ($partner->isMigrationCompleted()) {
            $this->orderService->setPartnerId($partner->id)->setOrderId($request->order)->setInterest($interest)
                ->setBankTransactionCharge($bank_transaction_charge)->update();
        } else {
            $pos_order = PosOrder::find($order);
            $pos_order->update(['interest' => $interest, 'bank_transaction_charge' => $bank_transaction_charge]);
        }
        return http_response($request, null, 200);
    }

    public function orderInvoiceDownload($order_id, Request $request)
    {
        $partner = $request->auth_user->getPartner();
        return $this->orderService->orderInvoiceDownload($partner->id, $order_id);
    }

    public function calculateEmiCharges(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'emi_month' => 'required',
        ]);
        return Calculations::getMonthData($request->amount, $request->emi_month, false);
    }

    public function createPayment($order, Request $request, PosOrderResolver $posOrderResolver)
    {
        $order = $posOrderResolver->setOrderId($order)->get();
        /** @var PaymentLinkController $payment_link */
        $payment_link = app(PaymentLinkController::class);
        $auth_user = $request->auth_user->getAvatar();
        $request->merge(array(
            'amount' => $order->due,
            'purpose' => $request->purpose,
            'customer_id' => $order->customer_id,
            'emi_month' => $request->emi_month,
            'interest_paid_by' => $request->interest_paid_by,
            'transaction_charge' => $request->transaction_charge,
            'pos_order_id' => $order->id,
            "type" => 'partner',
            'user' => $auth_user,
            'partner' => $auth_user
        ));
        $data = $payment_link->store($request)->getData(true);
        return http_response($request, null, $data['code'], $data);
    }

}
