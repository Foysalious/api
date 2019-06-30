<?php namespace App\Transformers;

use App\Models\PurchaseRequestApproval;
use League\Fractal\TransformerAbstract;

class PurchaseRequestApprovalTransformer extends TransformerAbstract
{
    public function transform(PurchaseRequestApproval $approval)
    {
        $member = $approval->member->profile;
        return [
            'name' => $member->name,
            'image' => $member->pro_pic,
            'mobile' => $member->mobile
        ];
    }
}