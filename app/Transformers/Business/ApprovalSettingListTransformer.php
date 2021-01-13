<?php namespace App\Transformers\Business;

use Illuminate\Foundation\Application;
use League\Fractal\TransformerAbstract;
use Sheba\Business\ApprovalSetting\ApprovalSettingDataFormat;

class ApprovalSettingListTransformer extends TransformerAbstract
{
    /**
     * @var Application|mixed
     */
    private $approvalSettingDataFormat;

    /**
     * ApprovalSettingListTransformer constructor.
     */
    public function __construct()
    {
        $this->approvalSettingDataFormat = app(ApprovalSettingDataFormat::class);
    }

    /**
     * @param $approval_setting
     * @return mixed
     */
    public function transform($approval_setting)
    {
        $this->approvalSettingDataFormat->initialize();
        $modules_data = $this->approvalSettingDataFormat->getModules($approval_setting->modules);
        $approvars_data = $this->approvalSettingDataFormat->getApprovers($approval_setting->approvers);

        return [
            'id' => $approval_setting->id,
            'business_id' => $approval_setting->business_id,
            'is_default' => $this->approvalSettingDataFormat->isDefault($approval_setting),
            'note' => $approval_setting->note,
            'target_type' => $this->approvalSettingDataFormat->getTargetTypes($approval_setting),
            'modules' => $modules_data,
            'is_all_modules' => $this->approvalSettingDataFormat->isAllModules(),
            'approvers' => $approvars_data,
            'approver_count' => count($approvars_data),
        ];
    }

}
