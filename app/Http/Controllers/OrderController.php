<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    private $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    public function getNotClosedOrderInfo($customer)
    {
        $customer = Customer::find($customer);
        $orders = $this->orderRepository->getOrderInfo($customer, '<>', 'Closed');
        return response()->json(['orders' => $orders, 'code' => 200, 'msg' => 'successful']);

    }
}
