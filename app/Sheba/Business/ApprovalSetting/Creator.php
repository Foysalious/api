<?php namespace Sheba\Business\ApprovalSetting;


use App\Models\Business;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\ModificationFields;

class Creator
{
     use ModificationFields;
    /**
     * @var ApprovalSettingRepository
     */
    private $approvalSettingRepo;
    /**
     * @var ApprovalSettingRequest
     */
    private $approvalSettingRequest;

    private $approvalSettingData=[];
    /**
     * @var Business
     */
    private $business;

    public function __construct(ApprovalSettingRepository $approval_setting_repo)
    {
        $this->approvalSettingRepo = $approval_setting_repo;
    }

    public function setApprovalSettingRequest(ApprovalSettingRequest $approval_setting_request)
    {
        $this->approvalSettingRequest = $approval_setting_request;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
    }

    public function create()
    {
        $this->approvalSettingRepo->create($this->withCreateModificationField($this->approvalSettingData));
        return $this;
    }

    private function makeData()
    {
        $this->approvalSettingData = [
            'business_id' => $this->business->id,
            'target_type' => $this->approvalSettingRequest->getTargetType(),
            'target_id' => $this->approvalSettingRequest->getTargetId(),
            'note' => $this->approvalSettingRequest->getNote(),
        ];
    }
}