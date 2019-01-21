<?php

namespace App\Transformers;


use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class QuestionTransformer extends TransformerAbstract
{
    public function transform($question)
    {
        if ($question instanceof Collection) {
            return [
                'title' => isset($question['title']) ? $question['title'] : '',
                'question' => isset($question['question']) ? $question['question'] : '',
                'answers' => isset($question['answers']) ? $question['answers'] : ''
            ];
        } else {
            return [
                'title' => isset($question->title) ? $question->title : '',
                'question' => $question->question,
                'answer' => isset($question->answers) ? $question->answers : $question->answer
            ];
        }
    }
}