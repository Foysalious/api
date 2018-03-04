<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class AnswerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Answer',
        'description' => 'Sheba service question answers'
    ];

    public function fields()
    {
        return [
            'answer' => ['type' => Type::string()]
        ];
    }
    public function resolveAnswerField($root){
//        dd($root);
    }
}