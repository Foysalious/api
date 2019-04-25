<?php namespace Sheba\Pos\Order;

use App\Models\PartnerPosSetting;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;

class QuickCreator
{
    /** @var array */
    private $data;
    /** @var PosOrderRepository */
    private $orderRepo;
    /** @var PosOrderItemRepository */
    private $itemRepo;
    /** @var PaymentCreator */
    private $paymentCreator;
    const QUICK_CREATE_DEFAULT_QUANTITY = 1;

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo, PaymentCreator $payment_creator)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->paymentCreator = $payment_creator;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function create()
    {
        $is_discount_applied = (isset($this->data['discount']) && $this->data['discount'] > 0);
        $setting = PartnerPosSetting::byPartner($this->data['partner']['id'])->first();

        $order_data['partner_id'] = $this->data['partner']['id'];
        $order_data['discount'] = $is_discount_applied ? ($this->data['is_percentage'] ? (($this->data['discount'] / 100) * $this->data['amount']) : $this->data['discount']) : 0;
        $order_data['discount_percentage'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] : 0) : 0;
        $order_data['customer_id'] = (isset($this->data['customer_id']) && $this->data['customer_id']) ? $this->data['customer_id'] : null;
        $order = $this->orderRepo->save($order_data);

        $service['pos_order_id'] = $order->id;
        $service['service_name'] = $this->data['name'];
        $service['unit_price'] = $this->data['amount'];
        $service['quantity'] = self::QUICK_CREATE_DEFAULT_QUANTITY;
        $service['vat_percentage'] = (isset($this->data['vat_percentage']) && $this->data['vat_percentage'] > 0) ?
            (double)$this->data['vat_percentage'] : ($setting ? (double)$setting->vat_percentage : 0.00);

        $this->itemRepo->save($service);

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'];
            $this->paymentCreator->credit($payment_data);
        }

        return $order;
    }
}