<?php namespace Tests\Unit\Sheba\Payments;


use App\Models\Customer;
use App\Models\Partner;
use App\Models\SubscriptionOrder;
use App\Models\Transport\TransportTicketOrder;
use Sheba\Payment\Adapters\Payable\RechargeAdapter;
use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Sheba\Payment\Adapters\Payable\TransportTicketPurchaseAdapter;
use Sheba\Payment\Adapters\Payable\UtilityOrderAdapter;
use Tests\Unit\UnitTestCase;

class Adapters extends UnitTestCase
{
    protected $database;

    protected $handler;

    public function test_recharge_adapter_with_correct_data()
    {
        $this->assertTrue((bool)(new RechargeAdapter(Partner::where('id', 37732)->first(), 0))->getPayable()); // Check te return value
    }

    public function test_transport_ticket_purchase_adapter_with_correct_data()
    {
        $id = TransportTicketOrder::orderBy('id', 'desc')->first()->id;
        $transport_ticket_order = TransportTicketOrder::find($id)->calculate();
        $this->assertTrue((bool)((new TransportTicketPurchaseAdapter())->setModelForPayable($transport_ticket_order)->getPayable())); // Check te return value
    }

    public function test_subscription_order_adapter_with_correct_data()
    {
        $order = SubscriptionOrder::orderBy('id', 'desc')->first();
        $customer = Customer::where('id',$order->customer_id)->first();
        $subscription_order = SubscriptionOrder::find((int)$order->id);
        $this->assertTrue((bool)((new SubscriptionOrderAdapter())->setModelForPayable($subscription_order)->setUser($customer)->getPayable())); // Check te return value
    }

    public function test_utility_order_adapter_with_correct_data()
    {
        $this->assertTrue((bool)((new UtilityOrderAdapter())->setUtilityOrder(1)->getPayable())); // Check te return value
    }


}