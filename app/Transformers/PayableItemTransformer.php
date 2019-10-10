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
        return [
            'id' => $payable['id'],
            'name' => Profile::find($payable['party']['profile_id'])->name,
            'amount' => (double)$payable['amount'],
            'amount_paid' => (double)$payable['amount_cleared'],
            "note" => $payable['note'],
            "created_at" => Carbon::parse($payable['created_at'])->format('Y-m-d h:s:i A')
        ];
    }
}
