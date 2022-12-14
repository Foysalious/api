<?php namespace Sheba\Reports\Complain;

use Sheba\Dal\Complain\Model as Complain;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    private static $columns = [
        'preset' => ['id', 'name', 'type_id', 'category_id'],
        'complainCategory' => ['id', 'name'],
        'complainType' => ['id', 'name', 'lifetime_sla'],
        'accessor' => ['id', 'name'],
        'customer' => ['id', 'profile_id', 'created_at'],
        'job' => ['id', 'partner_order_id', 'category_id', 'crm_id', 'service_id', 'service_name', 'status', 'created_at'],
        'resource' => ['id', 'profile_id'],
        'profile' => ['id', 'name', 'mobile'],
        'category' => ['id', 'parent_id', 'name'],
        'jobServices' => ['id', 'job_id', 'name'],
        'partnerOrder' => ['id', 'order_id', 'partner_id', 'created_at'],
        'order' => ['id', 'sales_channel', 'customer_id', 'created_at'],
        'partner' => ['id', 'name'],
        'user' => ['id', 'name', 'department_id', 'is_cm', 'is_active']
    ];

    public function build()
    {
        return $this->optimizedQuery();
    }

    private function normalQuery()
    {
        return Complain::with('preset.complainCategory', 'customer', 'partner', 'job');
    }

    private function optimizedQuery()
    {
        return Complain::with([
            'preset' => function ($preset_query) {
                $preset_query->select(self::$columns['preset'])->with([
                    'complainCategory' => function ($complain_category_query) {
                        $complain_category_query->select(self::$columns['complainCategory']);
                    },
                    'complainType' => function ($complain_type_query) {
                        $complain_type_query->select(self::$columns['complainType']);
                    }
                ]);
            },
            'customer' => function ($customer_query) {
                $customer_query->select(self::$columns['customer'])->with([
                    'profile' => function ($profile_query) {
                        $profile_query->select(self::$columns['profile']);
                    }
                ]);
            },
            'partner' => function ($partner_query) {
                $partner_query->select(self::$columns['partner']);
            },
            'accessor' => function ($accessor_query) {
                $accessor_query->select(self::$columns['accessor']);
            },
            'assignedTo' => function ($assignedTo_query) {
                $assignedTo_query->select(self::$columns['user']);
            },
            'job' => function ($job_query) {
                $job_query->select(self::$columns['job'])->with([
                    'partnerOrder' => function ($partner_order_query) {
                        $partner_order_query->select(self::$columns['partnerOrder'])->with([
                            'order' => function ($order_query) {
                                $order_query->select(self::$columns['order']);
                            }
                        ]);
                    },
                    'resource' => function ($resource_query) {
                        $resource_query->select(self::$columns['resource'])->with([
                            'profile' => function ($profile_query) {
                                $profile_query->select(self::$columns['profile']);
                            }
                        ]);
                    },
                    'jobServices' => function ($jobServices_query) {
                        $jobServices_query->select(self::$columns['jobServices']);
                    },
                    'category' => function ($category_query) {
                        $category_query->select(self::$columns['category']);
                    },
                    'crm' => function ($crm_query) {
                        $crm_query->select(self::$columns['user']);
                    }


                ]);
            }
        ]);
    }

}