<?php namespace Sheba\Partner\Service;


use Sheba\Dal\Category\Category;
use App\Models\Partner;
use Sheba\Dal\Service\Service;

class ServiceList
{
    /** @var Partner */
    private $partner;
    public $locationId;
    public $categoryId;

    /**
     * @param mixed $categoryId
     * @return ServiceList
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $locationId
     * @return $this
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function get()
    {
        return Service::whereHas('partnerServices', function ($q) {
            $q->selct('*');
        })
            ->select('id', 'name', 'bn_name')
            ->where('category_id', $this->categoryId)->get();
    }
}