<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Pos\Notifier\SmsHandler;

class WebstoreOrderSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $orderId;
    private $order;
    protected $tries = 1;
    private $data = [];
    /**
     * @var Partner
     */
    private $partner;
    private $serviceBreakDown = [];
    private $due_amount;
    private $template;

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
        if (!$this->partner->isMigrated(Modules::POS))
            $this->generateDataForOldWebstoreSms();
        else
            $this->generateDataForNewWebstoreSms();
        $handler->setPartner($this->partner)->setData($this->data)->handle();
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
        $this->data['feature_type'] = FeatureType::WEB_STORE;
        $this->data['business_type'] = BusinessType::SMANAGER;
        $this->data['wallet'] = $this->partner->wallet;
        $this->data['model'] = $this->partner;
    }

    private function generateDataForOldWebstoreSms()
    {
        $invoice_link =   $this->order->invoice ? : $this->resolveInvoiceLink();
        if ($this->order->status == OrderStatuses::PROCESSING)
            $this->template = 'pos-order-accept-customer';
        elseif ($this->order->status == OrderStatuses::CANCELLED || $this->order->status == OrderStatuses::DECLINED)
            $this->template = 'pos-order-cancelled-customer';
        elseif ($this->order->status == OrderStatuses::SHIPPED)
            $this->template = 'pos-order-shipped-customer';
        elseif ($this->order->status == OrderStatuses::COMPLETED)
            $this->template = 'pos-order-delivered-customer';
        else
            $this->template = 'pos-order-place-customer';

        $this->data['template'] = $this->template;
        $this->data['mobile'] = $this->order->customer->profile->mobile;
        $this->data['order_id'] = $this->order->id;
        $this->data['log'] = " BDT has been deducted for sending pos order update sms to customer(order id: {$this->order->id})";
        if ($this->template == 'pos-order-place-customer')
            $this->data['message'] = [
                'order_id' => $this->order->partner_wise_order_id,
                'net_bill' => $this->order->getNetBill(),
                'payment_status' => $this->order->getPaid() ? 'প্রদত্ত' : 'বকেয়া',
                'store_name' => $this->partner->name,
                'invoice_link' => $invoice_link
            ];
        else
            $this->data['message'] = [
                'order_id' => $this->order->partner_wise_order_id
            ];
    }

    private function generateDataForNewWebstoreSms()
    {
        if ($this->order['status'] == OrderStatuses::PROCESSING)
            $this->template = 'pos-order-accept-customer';
        elseif ($this->order['status'] == OrderStatuses::CANCELLED || $this->order->status == OrderStatuses::DECLINED)
            $this->template = 'pos-order-cancelled-customer';
        elseif ($this->order['status'] == OrderStatuses::SHIPPED)
            $this->template = 'pos-order-shipped-customer';
        elseif ($this->order['status'] == OrderStatuses::COMPLETED)
            $this->template = 'pos-order-delivered-customer';
        else
            $this->template = 'pos-order-place-customer';

        $this->data['template'] = $this->template;
        $this->data['mobile'] = $this->order['customer']['mobile'];
        $this->data['order_id'] = $this->order['partner_wise_order_id'];
        $this->data['log'] = " BDT has been deducted for sending pos order update sms to customer(order id: {$this->order['id']})";
        if ($this->template == 'pos-order-place-customer')
            $this->data['message'] = [
                'order_id' => $this->order['partner_wise_order_id'],
                'net_bill' => $this->order['price']['original_price'],
                'payment_status' => $this->order['price']['due'] > 0 ? 'প্রদত্ত' : 'বকেয়া',
                'store_name' => $this->partner->name,
                'invoice_link' => $this->order['invoice']
            ];
        else
            $this->data['message'] = [
                'order_id' => $this->order['partner_wise_order_id']
            ];
    }

    private function resolveInvoiceLink()
    {
        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class)->setPosOrder($this->order);
        return $invoiceService->generateInvoice()->saveInvoiceLink()->getInvoiceLink();
    }
}
