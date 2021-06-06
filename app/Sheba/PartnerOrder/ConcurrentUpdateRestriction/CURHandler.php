<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

use App\Models\PartnerOrder;

class CURHandler
{
    private $list;

    public function __construct(CURDataInterface $list)
    {
        $this->list = $list;
    }

    public function check(PartnerOrder $partner_order)
    {
        return $this->list->check($partner_order->id);
    }

    public function set(PartnerOrder $partner_order)
    {
        return $this->list->set($partner_order->id);
    }

    public function get()
    {
        return $this->list->get();
    }

    public function getCUObject(PartnerOrder $partner_order)
    {
        return $this->list->getCUObject($partner_order->id);
    }

    public function remove(PartnerOrder $partner_order)
    {
        return $this->list->remove($partner_order->id);
    }

    public function checkArray($partner_order_ids)
    {
        return $this->list->checkArray($partner_order_ids);
    }

    public function getExistedKeys()
    {
        return $this->list->getExistedKeys();
    }

    public function setArray($partner_order_ids)
    {
        return $this->list->setArray($partner_order_ids);
    }

    public function removeArray($partner_order_ids)
    {
        return $this->list->removeArray($partner_order_ids);
    }
}
