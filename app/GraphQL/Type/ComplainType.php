<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ComplainType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Complain',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'complain' => ['type' => Type::string()],
            'status' => ['type' => Type::string()],
            'code' => ['type' => Type::string()],
            'created_at' => ['type' => Type::string()],
            'created_at_timestamp' => ['type' => Type::int()],
            'preset' => ['type' => GraphQL::type('ComplainPreset')],
            'comments' => ['type' => Type::listOf(GraphQL::type('Comment'))],
        ];
    }

    protected function resolveCodeField($root)
    {
        return $root->code();
    }

    protected function resolveCreatedAtField($root)
    {
        return $root->created_at->format('M jS, Y');
    }

    protected function resolveCreatedAtTimestampField($root)
    {
        return $root->created_at->timestamp;
    }

    protected function resolvePresetField($root)
    {
        return $root->preset;
    }

    protected function resolveCommentsField($root)
    {
        $root->load(['comments' => function ($q) {
            $q->with('commentator');
        }]);
        return $root->comments;
    }
}