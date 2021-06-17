<?php namespace App\Http\Controllers\PosOrder;


use App\Http\Controllers\Controller;
use App\Http\Controllers\VoucherController;
use App\Sheba\PosOrderService\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $orderService;


    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $hasQueryStr = strpos($request->getRequestUri(), '?');
        $queryStr = $hasQueryStr ? '?'.substr($request->getRequestUri(), strpos($request->getRequestUri(), "?") + 1) : '';
        $order = $this->orderService->setPartnerId($partner->id)->setFilterParams($queryStr)->getOrderList();
        if(!$order) return api_response($request, "অর্ডারটি পাওয়া যায় নি", 404, $order);
        else return api_response($request, null, 200, $order);

    }

    public function show(Request $request, $order_id)
    {
        $partner = $request->auth_user->getPartner();
        $orderDetails = $this->orderService->setPartnerId($partner->id)->setOrderId($order_id)->getDetails();
        if(!$orderDetails) return api_response($request, "অর্ডারটি পাওয়া যায় নি", 404, $orderDetails);
        else return api_response($request, null, 200, $orderDetails);
    }

    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->orderService
            ->setPartnerId($partner->id)
            ->setCustomerId($request->customer_id)
            ->setDeliveryAddress($request->delivery_address)
            ->setSalesChannelId($request->sales_channel_id)
            ->setDeliveryCharge($request->delivery_charge)
            ->setStatus($request->status)
            ->setSkus($request->skus)
            ->setDiscount($request->discount)
            ->setPaymentMethod($request->payment_method)
            ->setPaymentLinkAmount($request->payment_link_amount)
            ->setPaidAmount($request->paid_amount)
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

}