<?php namespace Sheba\Checkout;

use App\Models\Category;
use App\Models\Partner;

class CommissionCalculator
{
    /** @var Category */
    private $category;
    /** @var Partner */
    private $partner;

    /** @var float */
    private $serviceCommission = null;

    /**
     * @param Category $category
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return float
     */
    public function getServiceCommission()
    {
        if ($this->serviceCommission) return $this->serviceCommission;

        $category_partner = ($this->category->partners()->wherePivot('partner_id', $this->partner->id)->first())->pivot;
        $this->serviceCommission = (double) $category_partner->commission;
        return $this->serviceCommission;
    }

    /**
     * @return float
     */
    public function getMaterialCommission()
    {
        return $this->category->use_partner_commission_for_material ?
            $this->getServiceCommission() :
            $this->category->material_commission_rate;
    }
}
