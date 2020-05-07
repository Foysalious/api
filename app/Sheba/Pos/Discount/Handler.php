<?php namespace Sheba\Pos\Discount;

use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Models\PosOrderDiscount;
use App\Models\PosOrderItem;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Pos\Discount\DTO\Params\Order;
use Sheba\Pos\Discount\DTO\Params\Service;
use Sheba\Pos\Discount\DTO\Params\Voucher;
use Sheba\Pos\Repositories\Interfaces\PosDiscountRepositoryInterface;

class Handler
{
    /** @var PosDiscountRepositoryInterface $posDiscountRepo */
    private $posDiscountRepo;
    private $type;
    /** @var array */
    private $data;
    /** @var PosOrder $order */
    private $order;
    /** @var PartnerPosService $partnerPosService */
    private $partnerPosService;
    /** @var PosOrderItem $orderItem */
    private $orderItem;
    /** @var PosOrderDiscount $discount */
    private $discount;
    /** @var array $updateData*/
    private $updateData;

    public function __construct(PosDiscountRepositoryInterface $pos_discount_repo)
    {
        $this->posDiscountRepo = $pos_discount_repo;
    }

    /**
     * @param PosOrder $order
     * @return $this
     */
    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param PartnerPosService $partner_pos_service
     * @return $this
     */
    public function setPosService(PartnerPosService $partner_pos_service)
    {
        $this->partnerPosService = $partner_pos_service;
        return $this;
    }

    /**
     * @param PosOrderItem $order_item
     * @return $this
     */
    public function setPosOrderItem(PosOrderItem $order_item)
    {
        $this->orderItem = $order_item;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     * @throws InvalidDiscountType
     */
    public function setType($type)
    {
        DiscountTypes::checkIfValid($type);
        $this->type = $type;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function hasDiscount()
    {
        if ($this->type == DiscountTypes::ORDER) {
            return isset($this->data['discount']) && $this->data['discount'] > 0;
        } else if ($this->type == DiscountTypes::SERVICE) {
            $additional_rules = true;
            if (isset($this->data['is_wholesale_applied'])) $additional_rules = (
                !$this->data['is_wholesale_applied'] ||
                ($this->data['is_wholesale_applied'] && !$this->partnerPosService->wholesale_price)
            );
            return $this->partnerPosService->discount() && $additional_rules;
        } else if ($this->type == DiscountTypes::VOUCHER) {
            return $this->data['is_valid'];
        }

        return false;
    }

    /**
     * @param PosOrder $order
     */
    public function create(PosOrder $order)
    {
        $discount_data = $this->getData();
        if (empty($discount_data)) return;
        $discount_data['pos_order_id'] = $order->id;
        $this->posDiscountRepo->create($discount_data);
    }

    /**
     * @param PosOrderDiscount $discount
     * @return $this
     */
    public function setDiscount(PosOrderDiscount $discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @param array $update_data
     * @return $this
     */
    public function setServiceDiscountData(array $update_data)
    {
        $this->updateData = $update_data;
        return $this;
    }

    public function update()
    {
        if ($this->discount) {
            $this->posDiscountRepo->update($this->discount, $this->updateData);
        }
    }

    public function getBeforeData()
    {
        $order_discount = null;
        if ($this->type == DiscountTypes::ORDER) {
            $order_discount = new Order();
            $order_discount->setType($this->type)
                ->setOriginalAmount($this->data['discount'])
                ->setIsPercentage($this->data['is_percentage']);
        } else if ($this->type == DiscountTypes::SERVICE) {
            $order_discount = new Service();
            $order_discount->setType($this->type)
                ->setDiscount($this->partnerPosService->discount())
                ->setAmount($this->partnerPosService->getDiscount() * $this->data['quantity']);
            return $order_discount->getBeforeData();
        } else if ($this->type == DiscountTypes::VOUCHER) {
            $order_discount = new Voucher();
            $order_discount->setType($this->type)
                ->setVoucher($this->data['voucher'])
                ->setAmount($this->data['amount']);
        }

        return $order_discount->getData();

    }

    public function getData()
    {
        $order_discount = null;
        if ($this->type == DiscountTypes::ORDER) {
            $order_discount = new Order();
            $order_discount->setOrder($this->order)
                ->setType($this->type)
                ->setOriginalAmount($this->data['discount'])
                ->setIsPercentage($this->data['is_percentage']);
        } else if ($this->type == DiscountTypes::SERVICE) {
            $order_discount = new Service();
            $order_discount->setType($this->type)
                ->setDiscount($this->partnerPosService->discount())
                ->setAmount($this->partnerPosService->getDiscount() * $this->data['quantity'])
                ->setPosOrderItem($this->orderItem);
        } else if ($this->type == DiscountTypes::VOUCHER) {
            $order_discount = new Voucher();
            $order_discount->setType($this->type)
                ->setVoucher($this->data['voucher'])
                ->setAmount($this->data['amount']);
        }

        return $order_discount->getData();
    }
}