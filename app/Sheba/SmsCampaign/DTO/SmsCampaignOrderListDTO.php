<?php namespace Sheba\SmsCampaign\DTO;

use Illuminate\Database\Eloquent\Collection;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrder;
use Sheba\PresentableDTO;

class SmsCampaignOrderListDTO extends PresentableDTO
{
    /** @var Collection */
    private $orders;

    public function __construct(Collection $orders = null)
    {
        if ($orders) $this->setOrders($orders);
    }

    public function setOrders(Collection $orders)
    {
        $this->orders = $orders;
        return $this;
    }

    public function toArray()
    {
        $orders = [];
        $this->orders->each(function (SmsCampaignOrder $order) use (&$orders) {
            $order_dto = new SmsCampaignOrderDTO($order);
            $orders[] = [
                'id' => $order_dto->getId(),
                'name' => $order_dto->getTitle(),
                'cost' => $order_dto->getTotalCost(),
                'created_at' => $order_dto->getFormattedCreatedAt()
            ];
        });
        return $orders;
    }
}