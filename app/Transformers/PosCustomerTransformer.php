<?php namespace App\Transformers;

use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use League\Fractal\TransformerAbstract;

class PosCustomerTransformer extends TransformerAbstract
{
    public function transform(PosCustomer $customer)
    {
        return [
            'id'    => $customer->id,
            'name'  => PartnerPosCustomer::getPartnerPosCustomerName($customer['partner_id'], $customer->id),
            'image' => $customer->profile->pro_pic,
            'mobile'=> $customer->profile->mobile,
            'email' => $customer->profile->email
        ];
    }
}
