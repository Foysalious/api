<?php namespace Sheba\Partner\Category;


use Sheba\Dal\Category\Category;
use App\Models\Partner;

class CategoryList
{
    /** @var Partner */
    private $partner;
    public $locationId;

    /**
     * @param Partner $partner
     * @return CategoryList
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $locationId
     * @return CategoryList
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function get()
    {
        $categories = Category::published()->whereHas('partners', function ($q) {
            $q->where([['category_partner.is_verified', 1], ['partner_id', $this->partner->id]]);
        })->whereHas('services', function ($q) {
            $q->published()->whereHas('locations', function ($q) {
                $q->published()->where('location_service.location_id', $this->locationId);
            });
        })->whereHas('locations', function ($q) {
            $q->published()->where('category_location.location_id', $this->locationId);
        })->select('id', 'name', 'bn_name', 'is_vat_applicable')->get();
        foreach ($categories as $category) {
            $category['is_car_rental'] = in_array($category->id, config('sheba.car_rental.secondary_category_ids')) ? 1 : 0;
            $category['vat_percentage'] = config('sheba.category_vat_in_percentage');
        }
        return $categories;
    }
}
