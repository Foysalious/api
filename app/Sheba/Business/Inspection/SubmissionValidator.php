<?php namespace Sheba\Business\Inspection;

use App\Models\BusinessMember;
use App\Models\Inspection;
use App\Sheba\Business\ACL\AccessControl;

class SubmissionValidator
{
    /** @var BusinessMember */
    private $businessMember;
    /** @var Inspection */
    private $inspection;
    private $accessControl;
    private $itemResult;
    private $message;

    public function __construct(AccessControl $access_control)
    {
        $this->accessControl = $access_control;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setInspection(Inspection $inspection)
    {
        $this->inspection = $inspection;
        return $this;
    }

    public function setItemResult($item_result)
    {
        $this->itemResult = collect($item_result);
        return $this;
    }

    public function hasError()
    {
        if (!$this->hasAccess()) {
            $this->message = "You're not authorized";
            return 1;
        }
        foreach ($this->inspection->items as $inspection_item) {
            $variables = json_decode($inspection_item->variables);
            $result = $this->itemResult->where('id', $inspection_item->id)->first();
            if ($variables->is_required && (!$result || empty($result->result))) {
                $this->message = $inspection_item->title . ' is required';
                return 1;
            }
            if ($inspection_item->isRadio() && $result->result == 'failed' && empty($result->comment)) {
                $this->message = $inspection_item->title . ' comment is required';
                return 1;
            }
        }
    }

    public function hasAccess()
    {
        return $this->accessControl->setBusinessMember($this->businessMember)->hasAccess('inspection.rw') || $this->inspection->member_id == $this->businessMember->member_id;
    }

    public function getErrorMessage()
    {
        return $this->message;
    }
}