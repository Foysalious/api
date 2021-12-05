<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\ExpenseTracker\EntryType;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Order\RefundNatures\NatureFactory;
use Sheba\Pos\Order\RefundNatures\Natures;
use Sheba\Pos\Order\RefundNatures\RefundNature;
use Sheba\Pos\Order\RefundNatures\ReturnNatures;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Usage\Usage;

class StatusChanger
{
    protected $status;
    /** @var PosOrder */
    protected $order;
    /** @var PosOrderRepository */
    protected $orderRepo;
    protected $refund_nature;
    protected $return_nature;
    /** @var PaymentCreator */
    protected $paymentCreator;
    protected $modifier;

    public function __construct(PosOrderRepository $order_repo, PaymentCreator $paymentCreator)
    {
        $this->orderRepo = $order_repo;
        $this->refund_nature = Natures::RETURNED;
        $this->return_nature = ReturnNatures::FULL_RETURN;
        $this->paymentCreator = $paymentCreator;
    }

    /**
     * @param PosOrder $order
     * @return StatusChanger
     */
    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    /**
     * @param $status
     * @return StatusChanger
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @throws ExpenseTrackingServerError
     */
    public function changeStatus()
    {
        $this->orderRepo->update($this->order, ['status' => $this->status]);
        if ($this->order->sales_channel == SalesChannels::WEBSTORE) {
            if ($this->status == OrderStatuses::DECLINED || $this->status == OrderStatuses::CANCELLED) $this->refund();
            if ($this->status == OrderStatuses::COMPLETED && $this->order->getDue()) $this->collectPayment($this->order);
        }
        return true;
    }

    private function getData()
    {
        $services = [];
        $this->order->items()->each(function ($item) use (&$services) {
            $service = [];
            $service['is_vat_applicable'] = $item->vat_percentage ? 1 : 0;
            $service['id'] = $item->id;
            $service['name'] = $item->service_name;
            $service['quantity'] = 0;
            array_push($services, $service);
        });
        return [
            'services' => json_encode($services),
            'is_refunded' => 1,
            'payment_method' => $this->order->payments()->first()->method ?? null,
            'paid_amount' => -1 * $this->order->calculate()->getPaid()
        ];
    }

    private function refund()
    {
        /** @var RefundNature $refund */
        $refund = NatureFactory::getRefundNature($this->order, $this->getData(), $this->refund_nature, $this->return_nature);
        $refund->setNew(1)->update();
    }

    /**
     * @param $order
     * @throws ExpenseTrackingServerError
     */
    private function collectPayment($order)
    {
        $payment_data = [
            'pos_order_id' => $order->id,
            'amount' => $order->getDue(),
            'method' => 'cod'
        ];
        if ($order->emi_month) $payment_data['emi_month'] = $order->emi_month;
        $this->paymentCreator->credit($payment_data);
        $order = $order->calculate();
        $order->payment_status = $order->getPaymentStatus();
        $this->updateIncome($order, $order->getDue(), $order->emi_month);
        /** USAGE LOG */
        (new Usage())->setUser($order->partner)->setType(Usage::Partner()::POS_DUE_COLLECTION)->create($this->modifier);
    }

    /**
     * @param PosOrder $order
     * @param $paid_amount
     * @param $emi_month
     * @throws ExpenseTrackingServerError
     */
    private function updateIncome(PosOrder $order, $paid_amount, $emi_month)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        $amount = (double)$order->getNetBill();
        $entry->setPartner($order->partner)->setAmount($amount)->setAmountCleared($paid_amount)->setFor(EntryType::INCOME)->setSourceType(class_basename($order))->setSourceId($order->id)->setCreatedAt($order->created_at)->setEmiMonth($emi_month)->setIsWebstoreOrder($order->sales_channel == SalesChannels::WEBSTORE ? 1 : 0)->updateFromSrc();
    }
}