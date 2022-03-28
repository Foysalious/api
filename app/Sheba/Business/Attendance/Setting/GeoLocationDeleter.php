<?php namespace App\Sheba\Business\Attendance\Setting;

use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepositoryInterface;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;

class GeoLocationDeleter
{
    /** @var BusinessOfficeRepositoryInterface $businessOfficeRepo */
    private $businessOfficeRepo;

    public function __construct(BusinessOfficeRepositoryInterface $business_office_repo)
    {
        $this->businessOfficeRepo = $business_office_repo;
    }

    /** @var BusinessOffice $businessOffice */
    private $businessOffice;

    public function setBusinessOfficeId($business_office_id)
    {
        $this->businessOffice = $this->businessOfficeRepo->find($business_office_id);
        return $this;
    }

    public function delete()
    {
        $this->businessOffice->delete();
    }

}
