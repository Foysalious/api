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

    private function setShebaLogisticDeliveryCharge()
    {
        if (!$this->category || !$this->categoryPartnerPivot || !is_null($this->shebaLogisticDeliveryCharge)) return $this;

        if ($this->category->needsLogistic() && $this->doesUseShebaLogistic()) {
            $this->shebaLogisticDeliveryCharge = $this->category->getShebaLogisticsPrice();
        }

        return $this;
    }

    public function doesUseShebaLogistic()
    {
        return (bool)$this->categoryPartnerPivot->uses_sheba_logistic;
    }

    public function get()
    {
        return $this->doesUseShebaLogistic() ?
            (double)$this->shebaLogisticDeliveryCharge :
            (double)$this->categoryPartnerPivot->delivery_charge;
    }
}