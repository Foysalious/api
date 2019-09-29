<?php namespace Sheba\Reports\Affiliation;

use App\Models\Affiliation;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    private static $columns = [
        'affiliation' => ['id', 'affiliate_id', 'customer_name', 'customer_mobile', 'service', 'status', 'is_fake', 'created_at', 'updated_at'],
        'affiliate' => ['id', 'profile_id', 'ambassador_id'],

        'order' => ['id', 'customer_id', 'affiliation_id', 'affiliation_cost', 'sales_channel'],
        'partnerOrders' => ['id', 'order_id', 'sheba_collection', 'partner_collection',
            'closed_at', 'closed_and_paid_at', 'cancelled_at'],
        'jobs' => ['id', 'partner_order_id', 'service_quantity', 'service_unit_price', 'commission_rate', 'delivery_charge', 'vat', 'discount',
            'sheba_contribution', 'partner_contribution', 'discount_percentage', 'status'],
        'usedMaterials' => ['id', 'job_id', 'material_price'],
        'jobServices' => ['id', 'job_id', 'service_id', 'quantity', 'min_price', 'unit_price', 'discount',
            'sheba_contribution', 'partner_contribution', 'discount_percentage'],

        'profile' => ['id', 'name', 'mobile'],
        'statusChangeLogs' => ['id', 'affiliation_id', 'to_status', 'created_at'],
        'logs' => ['id', 'affiliation_id', 'created_at'],
    ];

    public function build()
    {
        return $this->optimizedQuery();
    }

    private function normalQuery()
    {
        return Affiliation::with('affiliate.profile', 'order', 'logs', 'statusChangeLogs');
    }

    private function optimizedQuery()
    {
        return Affiliation::select(self::$columns['affiliation'])
            ->with([
                'affiliate' => function ($affiliate_query) {
                    $affiliate_query->select(self::$columns['affiliate'])->with([
                        'profile' => function ($profile_query) {
                            $profile_query->select(self::$columns['profile']);
                        }
                    ]);
                },
                'order' => function ($order_query) {
                    $order_query->select(self::$columns['order'])->with([
                        'partnerOrders' => function ($partner_order_query) {
                            $partner_order_query->select(self::$columns['partnerOrders'])->with([
                                'jobs' => function ($job_query) {
                                    $job_query->select(self::$columns['jobs'])->with([
                                        'jobServices' => function ($job_service_query) {
                                            $job_service_query->select(self::$columns['jobServices']);
                                        },
                                        'usedMaterials' => function ($used_material_query) {
                                            $used_material_query->select(self::$columns['usedMaterials']);
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);
                },
                'logs' => function ($log) {
                    $log->select(self::$columns['logs']);
                },
                'statusChangeLogs' => function ($status_change_log) {
                    $status_change_log->select(self::$columns['statusChangeLogs']);
                }
            ]);
    }

}