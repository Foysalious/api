<?php namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class IncomeTransformer extends TransformerAbstract
{
    /**
     * @param array $income
     * @return array
     */
    public function transform(array $income)
    {
        return [
            'id' => $income['id'],
            "amount" => (double)$income['amount'],
            "due" => (double)($income['amount'] - $income['amount_cleared']),
            "type" => $income['type'],
            "created_at" => Carbon::parse($income['entry_at'])->format('Y-m-d h:i:s A'),
            "head" => [
                "id" => $income['head']['id'],
                "name" => [
                    'en' => $income['head']['name'],
                    'bn' => $income['head']['name_bn']
                ]
            ],
            "note" => $income['note']
        ];
    }
}
