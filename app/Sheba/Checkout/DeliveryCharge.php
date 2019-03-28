<?php namespace Sheba\Checkout;

use App\Models\Category;
use App\Models\Partner;

class DeliveryCharge
{
    /** @var Partner */
    private $partner;
    /** @var Category */
    private $category;
    private $shebaLogisticDeliveryCharge;
    private $categoryPartnerPivot;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        if ($this->category->needsLogistic())
            $this->shebaLogisticDeliveryCharge = $this->category->getShebaLogisticsPrice();

        return $this;
    }

    public function setCategoryPartnerPivot($category_partner_pivot)
    {
        $this->categoryPartnerPivot = $category_partner_pivot;
        return $this;
    }

    public function getDeliveryCharge()
    {
        if ((int)$this->categoryPartnerPivot->uses_sheba_logistic) {
            return $this->shebaLogisticDeliveryCharge;
        } else {
            return (double)$this->categoryPartnerPivot->delivery_charge;
        }
    }
}