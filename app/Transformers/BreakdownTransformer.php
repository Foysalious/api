<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class BreakdownTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'questions'
    ];

    public function transform($breakdown)
    {
        return [
            'id' => $breakdown['id'],
            'name' => $breakdown['name'],
            'option' => $breakdown['option'],
            'quantity' => $breakdown['quantity'],
            'unit' => $breakdown['unit'],
            'original_price' => $breakdown['original_price'],
            'discounted_price' => $breakdown['discounted_price'],
            'discount' => $breakdown['discount'],
        ];
    }

    public function includeQuestions($breakdown)
    {
        $collection = $this->collection($breakdown['questions'], new QuestionTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}