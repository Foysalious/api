<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CommentType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Comment',
        'description' => 'Comments'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'commentator_name' => ['type' => Type::string()],
            'commentator_picture' => ['type' => Type::string()],
            'commentator_type' => ['type' => Type::string()],
            'comment' => ['type' => Type::string()],
        ];
    }

    protected function resolveCommentatorTypeField($root)
    {
        return strtolower(str_replace('App\Models\\', "", $root->commentator_type));
    }

    protected function resolveCommentatorNameField($root)
    {
        if (class_basename($root->commentator) == 'User') {
            return $root->commentator->name;
        } else {
            return $root->commentator->profile->name;
        }
    }

    protected function resolveCommentatorPictureField($root)
    {
        if (class_basename($root->commentator) == 'User') {
            return $root->commentator->name;
        } else {
            return $root->commentator->profile->name;
        }
    }

}