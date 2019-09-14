<?php namespace Sheba\Pos\Discount;

use App\Models\PartnerPosService;
use App\Models\PosOrder;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Pos\Discount\DTO\Params\Order;
use Sheba\Pos\Discount\DTO\Params\Service;
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
                ->setAmount($this->partnerPosService->getDiscount() * $this->data['quantity']);
        }

        return $order_discount->getData();
    }
}