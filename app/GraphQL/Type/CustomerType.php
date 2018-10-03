<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use GraphQL;

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
            'created_at_timestamp' => ['type' => Type::string()],
            'referral_code' => ['type' => Type::string()],
            'user_hash' => ['type' => Type::string()],
            'credit' => ['type' => Type::float()],
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

    protected function resolveCreditField($root, $args)
    {
        return $root->wallet;
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

    protected function resolveUserHashField($root, $args)
    {
        return hash_hmac('sha256', $root->id, env('INTERCOM_SECRET_KEY'));
    }

    protected function resolveReferralCodeField($root, $args)
    {
        return $root->referral->code;
    }

    protected function resolveCreatedAtTimestampField($root, $args)
    {
        return $root->created_at->timestamp;
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
        if (isset($args['offset']) && isset($args['limit'])) {
            $offset = $args['offset'];
            $limit = $args['limit'];
        } else {
            list($offset, $limit) = calculatePagination(\request());
        }
        $root->load(['orders' => function ($q) use ($filter, $offset, $limit) {
            $q->select('id', 'customer_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address', 'created_at')->orderBy('id', 'desc')->skip($offset)->take($limit)
                ->with(['location', 'customer.profile', 'partnerOrders' => function ($q) use ($filter, $offset, $limit) {
                    if ($filter) {
                        $q->$filter();
                    }
                    $q->with(['partner', 'jobs' => function ($q) {
                        $q->with(['statusChangeLogs', 'complains', 'category', 'usedMaterials', 'jobServices.service', 'review', 'resource.profile']);
                    }]);
                }]);
        }]);
        $orders = $root->orders;
        $final = [];
        foreach ($orders as $order) {
            $partnerOrders = $order->partnerOrders;
            $cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at != null;
            })->sortByDesc('cancelled_at');
            $not_cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at == null;
            })->sortByDesc('id');
            $partnerOrder = $not_cancelled_partnerOrders->count() == 0 ? $cancelled_partnerOrders->first() : $not_cancelled_partnerOrders->first();
            $partnerOrder['order'] = $order;
            array_push($final, $partnerOrder);
        }
        $final = collect($final);
        $cancelled_served_partnerOrders = $final->filter(function ($order) {
            return $order->cancelled_at != null || $order->closed_at != null;
        });
        $others = $final->filter(function ($order) {
            return $order->cancelled_at == null && $order->closed_at == null;
        });
        $final = $others->merge($cancelled_served_partnerOrders)->values()->all();
        return $final;
    }

}