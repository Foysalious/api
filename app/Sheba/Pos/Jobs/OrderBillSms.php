<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\SmsHandler;

class OrderBillSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $orderId;
    private $order;
    protected $tries = 1;
    /**
     * @var array
     */
    private $data;

    /**
     * @var Partner
     */
    private $partner;

    public function __construct($partner,$orderId)
    {
        $this->partner = $partner;
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     * @param SmsHandler $handler
     * @throws Exception
     */
    public function handle(SmsHandler $handler)
    {
        if ($this->attempts() > 2) return;
        $this->resolvePosOrder();
        if(!$this->partner->isMigrationCompleted())
            $this->generateDataForOldSystem();
        else{

            $this->generateDataForNewSystem();
        }


        $handler->setData($this->data)->handle();
    }

    private function resolvePosOrder()
    {
        if(!$this->partner->isMigrationCompleted())
            $this->order = PosOrder::find($this->orderId);
        else
            $this->order = $this->getOrderDetailsFromPosOrderService();
    }


    private function getOrderDetailsFromPosOrderService()
    {
        /** @var OrderService $orderService */
        $orderService  = app(OrderService::class);
        return $orderService->setPartnerId($this->partner->id)->setOrderId($this->orderId)->getDetails();
    }

    private function generateDataForOldSystem()
    {
        $partner = $this->order->partner;
        $partner->reload();
        $service_break_down = [];
        $this->order->items->each(function ($item) use (&$service_break_down) {
            $service_break_down[$item->id] = $item->service_name . ': ' . $item->getTotal();
        });
        $due_amount = $this->order->getDue();
        $service_break_down = implode(',', $service_break_down);
        $data = [];
        $data['mobile'] = $this->order->customer->profile->mobile;
        $data['order_id'] = $this->order->partner_wise_order_id;
        $data['service_break_down'] = $service_break_down;
        $data['template'] = $due_amount > 0 ? 'pos-due-order-bills' : 'pos-order-bills';
        $data['vendor'] = 'infobip';
        $data['feature_type'] = FeatureType::POS;
        $data['business_type'] = BusinessType::SMANAGER;
        $data['wallet'] = $this->partner->wallet;
        $data['log'] = " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})";
        $message_data = $this->getMessageData($service_break_down,$due_amount);
        array_push($data,$message_data);
        return $this->data = $data;
    }

    private function generateDataForNewSystem()
    {
        $partner = $this->order->partner;
        $partner->reload();
        $service_break_down = [];
        $this->order->items->each(function ($item) use (&$service_break_down) {
            $service_break_down[$item->id] = $item->service_name . ': ' . $item->getTotal();
        });
        $due_amount = $this->order->getDue();
        $service_break_down = implode(',', $service_break_down);
        $data = [];
        $data['mobile'] = $this->order->customer->profile->mobile;
        $data['order_id'] = $this->order->partner_wise_order_id;
        $data['service_break_down'] = $service_break_down;
        $data['template'] = $due_amount > 0 ? 'pos-due-order-bills' : 'pos-order-bills';
        $data['vendor'] = 'infobip';
        $data['feature_type'] = FeatureType::POS;
        $data['business_type'] = BusinessType::SMANAGER;
        $data['wallet'] = $this->partner->wallet;
        $data['log'] = " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})";
        $message_data = $this->getMessageData($service_break_down,$due_amount);
        array_push($data,$message_data);
        return $this->data = $data;


    }

    private function getMessageData($service_break_down,$due_amount)
    {
        $data = [
            'order_id' => $this->order->partner_wise_order_id,
            'service_break_down' => $service_break_down,
            'total_amount' => $this->order->getNetBill(),
            'partner_name' => $this->order->partner->name,
        ];
        if($due_amount > 0 )
            array_push($data, ['total_due_amount' => $due_amount]);
        return $data;
    }
}
