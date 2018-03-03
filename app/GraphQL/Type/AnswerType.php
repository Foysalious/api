<?php

namespace App\GraphQL\Type;


use MongoDB\BSON\Type;

class AnswerType
{
    protected $attributes = [
        'name' => 'Answer',
        'description' => 'Sheba service question answers'
    ];

    public function fields($root)
    {
        return [
            'answer' => ['type' => Type::string()]
        ];
    }
    public function resolveAnswerField($root){
//        dd($root);
    }
}