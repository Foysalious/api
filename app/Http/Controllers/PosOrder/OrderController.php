<?php namespace App\Http\Controllers\PosOrder;


use App\Http\Controllers\Controller;
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
            ->store();
        return http_response($request, null, 200, $response);


    }

}