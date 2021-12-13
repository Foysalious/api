<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use App\Models\PosOrder;
use App\Sheba\PosOrderService\Services\OrderService;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\SmsHandler;
use App\Sheba\UserMigration\Modules;
use App\Sheba\Pos\Order\Invoice\InvoiceService;

class OrderBillSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $orderId;
    private $order;
    protected $tries = 1;
    private $data = [];
    private $partner;
    private $serviceBreakDown = [];
    private $due_amount;

    public function __construct($partner, $orderId)
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
        $this->generateCommonData();
        if (!$this->partner->isMigrated(Modules::POS)) $this->generateDataForOldSystem();
        else $this->generateDataForNewSystem();
        $handler->setData($this->partner)->setData($this->data)->handle();
    }

    private function resolvePosOrder()
    {
        if (!$this->partner->isMigrated(Modules::POS))
            $this->order = PosOrder::find($this->orderId);
        else
            $this->order = $this->getOrderDetailsFromPosOrderService();
    }


    private function getOrderDetailsFromPosOrderService()
    {
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($this->partner->id)->setOrderId($this->orderId)->getDetails()['order'];
    }

    private function generateCommonData()
    {
        $this->partner->reload();
        $this->data['template'] = $this->due_amount > 0 ? 'pos-due-order-bills' : 'pos-order-bills';
        $this->data['feature_type'] = FeatureType::POS;
        $this->data['business_type'] = BusinessType::SMANAGER;
        $this->data['wallet'] = $this->partner->wallet;
        $this->data['model'] = $this->partner;
    }

    private function generateDataForOldSystem()
    {
        $this->order->items->each(function ($item) {
            $this->serviceBreakDown[$item->id] = $item->service_name . ': ' . $item->getTotal();
        });
        $this->data['service_break_down'] = implode(',', $this->serviceBreakDown);
        $this->due_amount = $this->order->getDue();
        $this->data['mobile'] = $this->order->customer->profile->mobile;
        $this->data['order_id'] = $this->order->id;
        $this->data['log'] = " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})";
        $this->getMessageDataForOldSystem();

    }

    private function generateDataForNewSystem()
    {
        $items = $this->order['items'];
        foreach ($items as $item)
            $this->serviceBreakDown[$item['id']] = $item['name'] . ': ' . ($item['quantity'] * $item['unit_price']);
        $this->data['service_break_down'] = $this->serviceBreakDown = implode(',', $this->serviceBreakDown);
        $this->due_amount = $this->order['price']['due'];
        $this->data['mobile'] = $this->order['customer']['mobile'];
        $this->data['order_id'] = $this->order['partner_wise_order_id'];
        $this->data['log'] = " BDT has been deducted for sending pos order details sms (order id: {$this->order['id']})";
        $this->getMessageDataForNewSystem();
    }


    private function getMessageDataForOldSystem()
    {
        $invoice_link =   $this->order->invoice ? : $this->resolveInvoiceLink();
        $data = [
            'order_id' => $this->order->partner_wise_order_id,
            'service_break_down' => $this->serviceBreakDown,
            'total_amount' => $this->order->getNetBill(),
            'partner_name' => $this->partner->name,
            'invoice_link' => $invoice_link
        ];
        if ($this->due_amount > 0)
            $data['total_due_amount'] = $this->due_amount;
        $this->data['message'] = $data;
    }

    private function getMessageDataForNewSystem()
    {
        $data = [
            'order_id' => $this->order['partner_wise_order_id'],
            'service_break_down' => $this->serviceBreakDown,
            'total_amount' => $this->order['price']['original_price'],
            'partner_name' => $this->partner->name,
            'invoice_link' => $this->order['invoice']
        ];
        if ($this->due_amount > 0) $data['total_due_amount'] = $this->due_amount;
        return $this->data['message'] = $data;
    }

    private function resolveInvoiceLink()
    {
        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class)->setPosOrder($this->order);
        return $invoiceService->generateInvoice()->saveInvoiceLink()->getInvoiceLink();
    }
}
