<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class LeaveBalanceRemainingTransformer extends TransformerAbstract
{
    private $leave_types;
    /** @var BusinessMember $businessMember */
    private $businessMember;

    /**
     * LeaveBalanceRemainingTransformer constructor.
     * @param $leave_types
     */
    public function __construct($leave_types)
    {
        $this->leave_types = $leave_types;
    }

    public function transform($business_member)
    {
        $this->businessMember = $business_member;
        return $this->calculate();
    }

    private function calculate()
    {
        $data = [];
        foreach ($this->leave_types as $leave_type) {
            if ($leave_type->trashed()) continue;
            $used_leave_days = $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type->id);
            $leave_type_total_days = $this->businessMember->getTotalLeaveDaysByLeaveTypes($leave_type->id);
            array_push($data, [
                'id' => $leave_type->id,
                'title' => $leave_type->title,
                'allowed_leaves' => (int)$leave_type_total_days,
                'used_leaves' => $used_leave_days,
                'remaining' => ($leave_type_total_days - $used_leave_days)
            ]);
        }

        return $data;
    }

}
