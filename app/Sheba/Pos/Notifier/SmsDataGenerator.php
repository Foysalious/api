<?php namespace Sheba\Pos\Notifier;


use App\Models\PosOrder;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;

class SmsDataGenerator
{
    private $orderId;
    private $order;
    protected $tries = 1;
    private $data = [];
    private $partner;
    private $due_amount;

    /**
     * @param mixed $orderId
     * @return SmsDataGenerator
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return SmsDataGenerator
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $this->resolvePosOrder();
        $this->generateCommonData();
        if (!$this->partner->isMigrated(Modules::POS)) {
            $this->generateDataForOldSystem();
        } else {
            $this->generateDataForNewSystem();
        }
        return $this->data;
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
        return $orderService->setPartnerId($this->partner->id)->setOrderId($this->orderId)->getDetailsWithInvoice()['order'];
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
        $this->due_amount = $this->order->getDue();
        $this->data['mobile'] = $this->order->customer->profile->mobile;
        $this->data['order_id'] = $this->order->id;
        $this->data['log'] = " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})";
        $this->getMessageDataForOldSystem();

    }


    private function generateDataForNewSystem()
    {
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
            'total_amount' => $this->order['price']['discounted_price'],
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