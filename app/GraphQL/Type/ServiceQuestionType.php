<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ServiceQuestionType extends GraphQlType
{
    protected $attributes = [
        'name' => 'ServiceQuestion',
        'description' => 'Sheba ServiceQuestion'
    ];

    public function fields()
    {
        return [
            'question' => ['type' => Type::string()],
            'answers' => ['type' => Type::listOf(GraphQlType::type('Answer'))],
            'input_type' => ['type' => Type::string()],
            'screen' => ['type' => Type::string()],
        ];
    }

    protected function resolveQuestionField($root)
    {
        return trim($root->question);
    }

    protected function resolveAnswersField($root)
    {
        return $root->answers;
    }

    protected function resolveInputTypeField($root)
    {
        $answers = explode(',', $root->answers);
        return count($answers) <= 4 ? "radiobox" : "selectbox";
    }

    protected function resolveScreenField($root)
    {
        $words = explode(' ', trim($root->question));
        return count($words) <= 5 ? "normal" : "slide";
    }
}