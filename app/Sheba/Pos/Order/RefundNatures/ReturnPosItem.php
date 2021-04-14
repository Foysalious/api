<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;
use Sheba\Dal\POSOrder\SalesChannels as POSOrderSalesChannel;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Payment\Creator as PaymentCreator;

abstract class ReturnPosItem extends RefundNature
{
    protected $details;
    protected $old_services;
    /** @var PaymentCreator $paymentCreator */
    protected $paymentCreator;
    /** @var PosOrder */
    private $oldOrder;

    public function __construct(LogCreator $log_creator, Updater $updater, PaymentCreator $payment_creator)
    {
        parent::__construct($log_creator, $updater);
        $this->paymentCreator = $payment_creator;
    }

    public function update()
    {
        $this->oldOrder     = clone $this->order;
        $this->old_services = $this->new ? $this->order->items->pluckMultiple([
            'quantity',
            'unit_price'
        ], 'id', true)->toArray() : $this->old_services = $this->order->items->pluckMultiple([
            'quantity',
            'unit_price'
        ], 'service_id', true)->toArray();;
        $this->updater->setOrder($this->order)->setData($this->data)->setNew($this->new)->update();
        if ($this->order->calculate()->getPaid()) $this->refundPayment();
        $this->generateDetails();
        $this->saveLog();
        try {
            $this->updateIncome($this->order);
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
        }
    }

    private function refundPayment()
    {
        if (isset($this->data['is_refunded']) && $this->data['is_refunded']) {
            $payment_data['order_id'] = $this->order->id;
            $payment_data['amount']       = $this->data['paid_amount'];
            if ($this->data['paid_amount'] > 0) {
                $payment_data['method'] = $this->data['payment_method'];
                $this->paymentCreator->credit($payment_data);
            } else {
                $payment_data['amount'] = abs($payment_data['amount']);
                $this->paymentCreator->debit($payment_data);
            }
        }
    }

    /**
     * GENERATE LOG DETAILS DATA
     * @param null $order
     */
    protected function generateDetails($order = null)
    {
        if(isset($order) && !isset($this->oldOrder)) $this->oldOrder = $order;
        $changes = [];
        $this->services->each(function ($service) use (&$changes) {
            $changes[$service->id]['qty']        = [
                'new' => (double)$service->quantity,
                'old' => (double)$this->old_services[$service->id]->quantity
            ];
            $changes[$service->id]['unit_price'] = (double)$this->old_services[$service->id]->unit_price;
        });
        $details['items']['changes']         = $changes;
        $details['items']['total_sale']      = $this->oldOrder->getNetBill();
        if($this->oldOrder->sales_channel == POSOrderSalesChannel::WEBSTORE && $this->oldOrder->delivery_charge) $details['items']['total_sale'] += $this->oldOrder->delivery_charge;
        $details['items']['vat_amount']      = $this->oldOrder->getTotalVat();
        $details['items']['returned_amount'] = isset($this->data['paid_amount']) ? $this->data['paid_amount'] : 0.00;
        $this->details                       = json_encode($details);
    }

    /**
     * @param PosOrder $order
     * @throws ExpenseTrackingServerError
     */
    private function updateIncome(PosOrder $order)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry  = app(AutomaticEntryRepository::class);
        $amount = (double)$order->calculate()->getNetBill();
        $entry->setPartner($order->partner)->setAmount($amount)->setAmountCleared($order->getPaid())->setHead(AutomaticIncomes::POS)->setSourceType(class_basename($order))->setSourceId($order->id)->updateFromSrc();
    }
}
