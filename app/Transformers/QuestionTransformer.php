<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class QuestionTransformer extends TransformerAbstract
{
    public function transform($question)
    {
        return [
            'title' => isset($question->title) ? $question->title : '',
            'question' => $question->question,
            'answer' => isset($question->answers) ? $question->answers : $question->answer
        ];
    }
}