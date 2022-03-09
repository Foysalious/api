<?php namespace App\Sheba\Business\Attendance\Setting;

use App\Models\Business;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepositoryInterface;
use Sheba\ModificationFields;

class GeoLocationUpdater
{
    use ModificationFields;

    /*** @var BusinessOfficeRepositoryInterface */
    private $businessOfficeRepo;
    /*** @var Business */
    private $business;
    private $name;
    private $radius;
    private $lat;
    private $long;
    /*** @var GeoLocationDeleter */
    private $geoLocationDeleter;
    private $businessOffice;

    public function __construct(BusinessOfficeRepositoryInterface $business_office_repo, GeoLocationDeleter $geo_location_deleter)
    {
        $this->businessOfficeRepo = $business_office_repo;
        $this->geoLocationDeleter = $geo_location_deleter;
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

    public function setBusinessOfficeId($business_office_id)
    {
        $this->businessOffice = $this->businessOfficeRepo->builder()->withTrashed()->find($business_office_id);
        $this->business = $this->businessOffice->business;

        return $this;
    }

    public function update()
    {
        $data = [
            'name' => $this->name,
            'location' => json_encode(['lat' => $this->lat, 'long' => $this->long, 'radius' => $this->radius])
        ];
        $this->businessOfficeRepo->update($this->businessOffice, $this->withUpdateModificationField($data));
    }
}
