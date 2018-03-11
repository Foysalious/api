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
            'answers' => ['type' => Type::string()],
            'input_type' => ['type' => Type::string()],
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
        if (($root->question)) return 'selectbox';
        $answers = explode(',', $root->answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }
}