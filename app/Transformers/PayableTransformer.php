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
            "head" => [
                "id" => $payable['head']['id'],
                "name" => [
                    'en' => $payable['head']['name'],
                    'bn' => $payable['head']['name_bn']
                ]
            ],
            "note" => $payable['note'],
            "created_at" => Carbon::parse($payable['created_at'])->format('Y-m-d h:i:s A')
        ];
    }
}
