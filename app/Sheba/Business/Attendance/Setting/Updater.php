<?php namespace Sheba\Business\Attendance\Setting;

use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepositoryInterface;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $member;
    /** @var BusinessOffice $businessOffice */
    private $businessOffice;
    /** @var BusinessOfficeRepositoryInterface $businessOfficeRepo */
    private $businessOfficeRepo;
    private $name;
    private $ip;
    private $existingOfficeByIp;
    /** @var bool $isNeedToRestoreOffice */
    private $isNeedToRestoreOffice = false;
    /** @var Deleter $deleter */
    private $deleter;

    /**
     * Updater constructor.
     * @param BusinessOfficeRepositoryInterface $business_office_repo
     * @param Deleter $deleter
     */
    public function __construct(BusinessOfficeRepositoryInterface $business_office_repo, Deleter $deleter)
    {
        $this->businessOfficeRepo = $business_office_repo;
        $this->deleter = $deleter;
    }
    
    public function update()
    {
        if ($this->isNeedToRestoreOffice) {
            $this->deleter->setBusinessOfficeId($this->businessOffice->id)->delete();
            $this->businessOffice = $this->existingOfficeByIp;
            $this->restore();
        }

        $data = ['name' => $this->name, 'ip' => $this->ip];
        $this->businessOfficeRepo->update($this->businessOffice, $this->withUpdateModificationField($data));
    }

    public function setBusinessOfficeId($business_office_id)
    {
        $this->businessOffice = $this->businessOfficeRepo->builder()->withTrashed()->find($business_office_id);
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;

        $this->existingOfficeByIp = $this->businessOfficeRepo->builder()->withTrashed()->where('ip', $this->ip)->first();
        if ($this->existingOfficeByIp && $this->existingOfficeByIp->id != $this->businessOffice->id) {
            if ($this->existingOfficeByIp->trashed()) $this->isNeedToRestoreOffice = true;
            else $this->setError(403, "$this->ip ip already allocate to " . $this->existingOfficeByIp->name . " office");
        }
        
        return $this;
    }

    public function restore()
    {
        return $this->businessOffice->restore();
    }
}
