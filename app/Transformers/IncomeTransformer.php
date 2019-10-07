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
            "type" => $income['type'],
            "created_at" => Carbon::parse($income['created_at'])->format('Y-m-d h:s:i A'),
            "head" => [
                "id" => $income['head']['id'],
                "name" => $income['head']['name']
            ],
            "note" => $income['note']
        ];
    }
}
