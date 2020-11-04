<?php namespace Sheba\Pos\Order;


use App\Models\PosOrder;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Pos\Order\RefundNatures\NatureFactory;
use Sheba\Pos\Order\RefundNatures\Natures;
use Sheba\Pos\Order\RefundNatures\RefundNature;
use Sheba\Pos\Order\RefundNatures\ReturnNatures;
use Sheba\Pos\Repositories\PosOrderRepository;

class StatusChanger
{
    protected $status;
    /** @var PosOrder */
    protected $order;
    /** @var PosOrderRepository */
    protected $orderRepo;
    protected $refund_nature;
    protected $return_nature;

    public function __construct(PosOrderRepository $order_repo)
    {
        $this->orderRepo = $order_repo;
        $this->refund_nature = Natures::RETURNED;
        $this->return_nature = ReturnNatures::FULL_RETURN;
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

    public function changeStatus()
    {
        $this->orderRepo->update($this->order, ['status' => $this->status]);
        if ($this->status == OrderStatuses::DECLINED || $this->status == OrderStatuses::CANCELLED) $this->refund();

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
}