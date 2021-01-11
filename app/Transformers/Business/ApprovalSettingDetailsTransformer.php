<?php namespace App\Transformers\Business;

use Illuminate\Foundation\Application;
use League\Fractal\TransformerAbstract;
use Sheba\Business\ApprovalSetting\ApprovalSettingDataFormat;
use Sheba\Business\ApprovalSetting\MakeDefaultApprovalSetting;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;

class ApprovalSettingDetailsTransformer extends TransformerAbstract
{
    /**
     * @var Application|mixed
     */
    private $defaultApprovalSetting;
    /**
     * @var Application|mixed
     */
    private $approvalSettingDataFormat;

    /**
     * ApprovalSettingListTransformer constructor.
     */
    public function __construct()
    {
        $this->defaultApprovalSetting = app(MakeDefaultApprovalSetting::class);
        $this->approvalSettingDataFormat = app(ApprovalSettingDataFormat::class);
    }

    /**
     * @param $approval_setting
     * @return mixed
     */
    public function transform(ApprovalSetting $approval_setting)
    {
        $modules_data = $this->approvalSettingDataFormat->getModules($approval_setting->modules);
        $approvars_data = $this->approvalSettingDataFormat->getApprovers($approval_setting->approvers);

        return [
            'id' => $approval_setting->id,
            'business_id' => $approval_setting->business_id,
            'is_default' => 0,
            'note' => $approval_setting->note,
            'target_type' => $this->approvalSettingDataFormat->getTargetTypes($approval_setting),
            'modules' => $modules_data,
            'approvers' => $approvars_data,
            'approver_count' => count($approvars_data),
        ];
    }
}
