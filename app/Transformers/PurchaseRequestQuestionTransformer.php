<?php namespace App\Transformers;

use App\Models\PurchaseRequestQuestion;
use League\Fractal\TransformerAbstract;

class PurchaseRequestQuestionTransformer extends TransformerAbstract
{
    public function transform(PurchaseRequestQuestion $question)
    {
        return [
            'question' => $question->title,
            'answer' => $question->result
        ];
    }
}