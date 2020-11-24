<?php namespace Sheba\Business\Attendance\Setting;

use App\Models\Business;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepositoryInterface;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $name;
    private $ip;
    /** @var Business $business */
    private $business;
    /** @var BusinessOfficeRepositoryInterface $businessOfficeRepo */
    private $businessOfficeRepo;
    /** @var Updater $updater */
    private $updater;
    private $isNeedToRestoreOffice = false;
    private $existingOfficeByIp;

    /**
     * Creator constructor.
     * @param BusinessOfficeRepositoryInterface $business_office_repo
     * @param Updater $updater
     */
    public function __construct(BusinessOfficeRepositoryInterface $business_office_repo, Updater $updater)
    {
        $this->businessOfficeRepo = $business_office_repo;
        $this->updater = $updater;
    }

    public function create()
    {
        if ($this->isNeedToRestoreOffice)
            return $this->updater->setBusinessOfficeId($this->existingOfficeByIp->id)->setName($this->name)->restore();

        $data = [
            'business_id' => $this->business->id,
            'name' => $this->name,
            'ip' => $this->ip,
            'location' => "{}"
        ];
        return $this->businessOfficeRepo->create($this->withCreateModificationField($data));
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;

        $this->existingOfficeByIp = $this->businessOfficeRepo->findByIpOnBusinessWithSoftDeleted($this->business, $this->ip);
        $this->isNeedToRestoreOffice = false;

        if ($this->existingOfficeByIp) {
            if ($this->existingOfficeByIp->trashed()) $this->isNeedToRestoreOffice = true;
            else $this->setError(403, "$this->ip ip already allocate to " . $this->existingOfficeByIp->name . " office");
        }

        return $this;
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

    public function resetError()
    {
        return $this->errorCode = null;
    }
}
