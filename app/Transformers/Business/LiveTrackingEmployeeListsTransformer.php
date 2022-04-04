<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class LiveTrackingEmployeeListsTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {

        return [
            'id' => $business_member->id
        ];
    }
}