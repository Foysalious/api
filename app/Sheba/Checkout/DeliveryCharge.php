<?php namespace Sheba\Checkout;

use App\Models\Category;
use App\Models\Partner;
use Sheba\Logistics\Repository\ParcelRepository;

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
        $this->shebaLogisticDeliveryCharge = $this->getShebaLogisticsPrice();
        return $this;
    }

    public function setCategoryPartnerPivot($category_partner_pivot)
    {
        $this->categoryPartnerPivot = $category_partner_pivot;
        return $this;
    }

    private function getShebaLogisticsPrice()
    {
        $parcel_repo = app(ParcelRepository::class);
        $parcel_details = $parcel_repo->findBySlug($this->category->logistic_parcel_type);

        return isset($parcel_details['price']) ? $parcel_details['price'] : 0;
    }

    public function getDeliveryCharge()
    {
        if ((int)$this->categoryPartnerPivot->uses_sheba_logistic) {
            return $this->category->needsTwoWayLogistic() ? $this->shebaLogisticDeliveryCharge * 2 : $this->shebaLogisticDeliveryCharge;
        } else {
            return (double)$this->categoryPartnerPivot->delivery_charge;
        }
    }
}