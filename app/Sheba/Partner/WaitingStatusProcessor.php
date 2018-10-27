<?php namespace Sheba\Partner;

use App\Models\Partner;

class WaitingStatusProcessor
{
    private $partner;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEligibleForWaiting()
    {
        return $this->isValidPartnerStatus() && $this->isCompanyNameExist() && $this->isCompanyPhoneExist() && $this->isOneLocationTagged() &&
            $this->isOneCategoryTagged() && $this->isOneServiceTagged() && $this->isOneAdminResource() &&
            $this->isOneHandyResource() && $this->isOneActiveOperationDay() && $this->isCompanyAddressExists() &&
            $this->isAdminAllInfoPresent();
    }

    private function isValidPartnerStatus()
    {
        return in_array($this->partner->status, ['Onboarded', 'Rejected']);
    }

    private function isCompanyNameExist()
    {
        return !empty($this->partner->name);
    }

    private function isCompanyPhoneExist()
    {
        return !!$this->partner->getContactNumber();
    }

    private function isOneLocationTagged()
    {
        return !$this->partner->locations->isEmpty();
    }

    private function isOneCategoryTagged()
    {
        return !$this->partner->categories->isEmpty();
    }

    private function isOneServiceTagged()
    {
        return !$this->partner->services->isEmpty();
    }

    private function isOneAdminResource()
    {
        return !$this->partner->admins->isEmpty();
    }

    private function isOneHandyResource()
    {
        return !$this->partner->handymanResources->isEmpty();
    }

    private function isOneActiveOperationDay()
    {
        return !$this->partner->workingHours->isEmpty();
    }

    private function isCompanyAddressExists()
    {
        return !empty($this->partner->address);
    }

    private function isAdminAllInfoPresent()
    {
        $admin_resource = $this->partner->admins()->first();
        return (
            $admin_resource &&
            $admin_resource->nid_no &&
            $admin_resource->nid_image &&
            $admin_resource->profile->pro_pic != getProfileDefaultAvatar()
        );
    }
}