<?php namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ReceivableTransformer extends TransformerAbstract
{
    /**
     * @param array $receivable
     * @return array
     */
    public function transform(array $receivable)
    {
        return [
            'id' => $receivable['id'],
            'profile_id' => $receivable['party']['profile_id'],
            "due" => (double)($receivable['amount'] - $receivable['amount_cleared']),
            "head" => [
                "id" => $receivable['head']['id'],
                "name" => [
                    'en' => $receivable['head']['name'],
                    'bn' => $receivable['head']['name_bn']
                ]
            ],
            "note" => $receivable['note'],
            "created_at" => Carbon::parse($receivable['created_at'])->format('Y-m-d h:i:s A'),
        ];
    }
}
