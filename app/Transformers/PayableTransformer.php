<?php namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PayableTransformer extends TransformerAbstract
{
    /**
     * @param array $payable
     * @return array
     */
    public function transform(array $payable)
    {
        return [
            'id' => $payable['id'],
            'profile_id' => $payable['party']['profile_id'],
            "due" => (double)($payable['amount'] - $payable['amount_cleared']),
            "note" => $payable['note'],
            "created_at" => Carbon::parse($payable['created_at'])->format('Y-m-d h:s:i A'),
        ];
    }
}
