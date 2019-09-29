<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;


class ComplimentType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Compliment',
        'description' => 'Sheba Location'
    ];

    public function fields()
    {
        return [
            'name' => ['type' => Type::string()],
            'badge' => ['type' => Type::string()],
            'question_id' => ['type' => Type::int()],
            'answer_id' => ['type' => Type::int()]
        ];
    }

    public function resolveNameField($root)
    {
        return $root->answer;
    }

    public function resolveBadgeField($root)
    {
        return $root->badge;
    }

    public function resolveQuestionIdField($root)
    {
        return $root->rate_question_id;
    }

    public function resolveAnswerIdField($root)
    {
        return $root->rate_answer_id;
    }
}