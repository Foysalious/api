<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use GraphQL;

class ResourceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Resource',
        'description' => 'Sheba Resource'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'profile' => ['type' => GraphQL::type('Profile')],
            'name' => ['type' => Type::string()],
            'picture' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
            'mobile' => ['type' => Type::string()],
            'gender' => ['type' => Type::string()],
            'birthday' => ['type' => Type::string()],
            'address' => ['type' => Type::string()],
        ];
    }

    protected function resolveAddressField($root, $args)
    {
        return $root->profile->address;
    }

    protected function resolveNameField($root, $args)
    {
        return $root->profile->name;
    }

    protected function resolvePictureField($root, $args)
    {
        return $root->profile->pro_pic;
    }

    protected function resolveEmailField($root, $args)
    {
        return $root->profile->email;
    }

    protected function resolveMobileField($root, $args)
    {
        return $root->profile->mobile;
    }

    protected function resolveGenderField($root, $args)
    {
        return $root->profile->gender;
    }

    protected function resolveBirthdayField($root, $args)
    {
        return $root->profile->dob;
    }
}