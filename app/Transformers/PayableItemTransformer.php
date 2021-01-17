<?php namespace App\Transformers;

use App\Models\Profile;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PayableItemTransformer extends TransformerAbstract
{
    /**
     * @param array $payable
     * @return array
     */
    public function transform(array $payable)
    {
        $profile_id = $payable['party']['profile_id'];
        $profile = Profile::with('posCustomer')->find($profile_id);
        return [
            'id' => $payable['id'],
            'customer' => [
                'id' => $profile->posCustomer->id,
                'name' => $profile->name,
                'image' => $profile->pro_pic
            ],
            'amount' => (double)$payable['amount'],
            'amount_paid' => (double)$payable['amount_cleared'],
            "note" => $payable['note'],
            "created_at" => Carbon::parse($payable['created_at'])->format('Y-m-d h:i:s A')
        ];
    }
}
