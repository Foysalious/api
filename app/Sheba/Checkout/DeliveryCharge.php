<?php namespace Sheba\Checkout;

use App\Models\Category;

class DeliveryCharge
{
    /** @var Category */
    private $category;
    private $shebaLogisticDeliveryCharge;
    private $categoryPartnerPivot;

    public function setCategory(Category $category)
    {
        $this->category = $category;
        $this->setShebaLogisticDeliveryCharge();
        return $this;
    }

    public function setCategoryPartnerPivot($category_partner_pivot)
    {
        $this->categoryPartnerPivot = $category_partner_pivot;
        $this->setShebaLogisticDeliveryCharge();
        return $this;
    }

    /**
     * Sheba Logistics delivery charge will be calculated if category
     * has logistics enabled as per business decision
     * @return $this
     */
    private function setShebaLogisticDeliveryCharge()
    {
        if (!$this->category || !is_null($this->shebaLogisticDeliveryCharge)) return $this;
        if ($this->doesUseShebaLogistic()) {
            $this->shebaLogisticDeliveryCharge = $this->category->getShebaLogisticsPrice();
        }

        return $this;
    }

    public function doesUseShebaLogistic()
    {
        return $this->category->needsLogistic();
    }

    /**
     *  Delivery charge will be calculated from category as per business decision
     * @return float
     */
    public function get()
    {
        return $this->doesUseShebaLogistic() ?
            (double)$this->shebaLogisticDeliveryCharge :
            (double)$this->category->delivery_charge;
    }
}
