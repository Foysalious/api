<?php namespace App\Transformers;

use App\Models\PosCustomer;
use League\Fractal\TransformerAbstract;

class PosCustomerTransformer extends TransformerAbstract
{
    public function transform(PosCustomer $customer)
    {
        return [
            'id'    => $customer->id,
            'name'  => $customer->profile->name,
            'image' => $customer->profile->pro_pic,
            'mobile'=> $customer->profile->mobile,
            'email' => $customer->profile->email
        ];
    }
}
