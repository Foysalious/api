<?php namespace Sheba\Business\ApprovalSetting;


use App\Models\Business;
use Illuminate\Foundation\Application;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingModule\Modules;

class FindApprovalSettings
{
    /**
     * @var Application|mixed
     */
    private $approvalSettingsRepo;

    public function __construct()
    {
        $this->approvalSettingsRepo = app(ApprovalSettingRepository::class);
    }

    /**
     * @param $business_member
     * @param $module
     * @return mixed|null
     */
    public function getApprovalSetting($business_member, $module)
    {
        /** @var Business $business */
        $business = $business_member->business;
        $approval_settings_with_leave = null;

        $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id)
            ->where('target_type', Targets::EMPLOYEE)->where('target_id', $business_member->id);
        if ($approval_settings->count() > 0) $approval_settings_with_leave = $this->getApprovalSettingWithLeave($approval_settings, $module);

        if ($approval_settings->count() == 0 || !$approval_settings_with_leave) {
            $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id)
                ->where('target_type', Targets::DEPARTMENT)->where('target_id', $business_member->department()->id);
            if ($approval_settings->count() > 0) $approval_settings_with_leave = $this->getApprovalSettingWithLeave($approval_settings, $module);
        }
        /*if ($approval_settings->count() == 0 || !$approval_settings_with_leave) {
            $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id)
                ->where('target_type', Targets::GENERAL_MODULE);
            if ($approval_settings->count() > 0) $approval_settings_with_leave = $this->getApprovalSettingWithLeave($approval_settings, $module);
        }*/
        if ($approval_settings->count() == 0 || !$approval_settings_with_leave) {
            $approval_settings = $this->approvalSettingsRepo->where('business_id', $business->id)
                ->where('target_type', Targets::GENERAL);
            if ($approval_settings->count() > 0) $approval_settings_with_leave = $this->getApprovalSettingWithLeave($approval_settings, $module);
        }

        return $approval_settings_with_leave;
    }

    /**
     * @param $approval_settings
     * @param $module
     * @return mixed
     */
    private function getApprovalSettingWithLeave($approval_settings, $module)
    {
        return $approval_settings->whereHas('modules', function ($module_query) use ($module) {
            $module_query->where('modules', $module);
        })->get()->last();
    }
}