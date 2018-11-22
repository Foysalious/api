<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class QuestionTransformer extends TransformerAbstract
{
    public function transform($question)
    {
        return [
            'question' => $question->question,
            'answer' => isset($question->answers) ? $question->answers : $question->answer
        ];
    }
}