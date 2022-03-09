<?php

namespace App\Sheba\Business\Attendance\Setting;

use App\Models\Business;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepositoryInterface;
use Sheba\ModificationFields;

class GeoLocationCreator
{
    use ModificationFields;

    /*** @var BusinessOfficeRepositoryInterface */
    private $businessOfficeRepo;
    /*** @var Business */
    private $business;
    private $name;
    private $radius;
    private $isNeedToRestoreOffice;
    private $lat;
    private $long;

    public function __construct(BusinessOfficeRepositoryInterface $business_office_repo)
    {
        $this->businessOfficeRepo = $business_office_repo;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    public function setLong($long)
    {
        $this->long = $long;
        return $this;
    }

    public function setRadius($radius)
    {
        $this->radius = $radius;
        return$this;
    }

    public function create()
    {
        $data = [
            'business_id' => $this->business->id,
            'name' => $this->name,
            'location' => json_encode(['lat' => $this->lat, 'long' => $this->long, 'radius' => $this->radius]),
            'is_location' => 1
        ];
        return $this->businessOfficeRepo->create($this->withCreateModificationField($data));
    }
}
