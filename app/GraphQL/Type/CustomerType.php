<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL;
use Illuminate\Http\Request;

class CustomerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Customer',
        'description' => 'Sheba customer'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'picture' => ['type' => Type::string()],
            'email' => ['type' => Type::string()],
            'mobile' => ['type' => Type::string()],
            'gender' => ['type' => Type::string()],
            'birthday' => ['type' => Type::string()],
            'address' => ['type' => Type::string()],
            'referral_code' => ['type' => Type::string()],
            'addresses' => ['type' => Type::listOf(GraphQL::type('Address'))],
            'profile' => ['type' => GraphQL::type('Profile')],
            'orders' => [
                'args' => [
                    'filter' => ['type' => Type::string()],
                    'offset' => ['type' => Type::int()],
                    'limit' => ['type' => Type::int()],
                ],
                'type' => Type::listOf(GraphQL::type('Order'))
            ]
        ];
    }


    protected function resolveAddressesField($root, $args)
    {
        return $root->delivery_addresses;
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

    protected function resolveReferralCodeField($root, $args)
    {
        return $root->referral->code;
    }

    protected function resolveOrdersField($root, $args)
    {
        $filter = null;
        if (isset($args['filter'])) {
            if ($args['filter'] === 'ongoing') {
                $filter = $args['filter'];
            } elseif ($args['filter'] === 'history') {
                $filter = $args['filter'];
            }
        }
        if ($filter === null) {
            return null;
        }
        if (isset($args['offset']) && isset($args['limit'])) {
            $offset = $args['offset'];
            $limit = $args['limit'];
        } else {
            list($offset, $limit) = calculatePagination(\request());
        }
        $root->load(['partnerOrders' => function ($q) use ($filter, $offset, $limit) {
            $q->$filter()->skip($offset)->take($limit)->orderBy('id', 'desc')->with(['partner', 'order' => function ($q) {
                $q->with('location', 'customer');
            }, 'jobs' => function ($q) {
                $q->with(['category', 'usedMaterials', 'jobServices']);
            }]);
        }]);
        return $root->partnerOrders;
    }

}