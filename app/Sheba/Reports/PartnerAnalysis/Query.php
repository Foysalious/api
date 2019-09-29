<?php namespace Sheba\Reports\PartnerAnalysis;

use App\Models\Partner;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    private static $columns = [
        'partner' => ['id', 'name', 'mobile', 'email', 'address', 'status', 'badge',
            'package_id', 'verified_at', 'created_at', 'created_by'],
        'subscription' => ['id', 'name'],
        'resource' => ['resources.id', 'profile_id', 'resources.is_verified'],
        'profile' => ['id', 'name', 'mobile', 'email'],
        'status_change_logs' => ['id', 'partner_id', 'to', 'created_at'],
    ];

    public function build()
    {
        return Partner::select(self::$columns['partner'])->with([
            'subscription' =>  function($subscription_query) {
                $subscription_query->select(self::$columns['subscription']);
            },
            'statusChangeLogs' => function($status_log_query) {
                $status_log_query->select(self::$columns['status_change_logs']);
            }, 'resources' => function($resource_query) {
                $resource_query->select(self::$columns['resource'])->with([
                    'profile' => function($profile_query) {
                        $profile_query->select(self::$columns['profile']);
                    }
                ])->withPivot('resource_type');
            }
        ])->withCount(['locations', 'categories', 'services', 'publishedServices']);
    }
}