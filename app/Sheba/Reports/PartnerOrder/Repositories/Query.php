<?php namespace Sheba\Reports\PartnerOrder\Repositories;

use App\Models\PartnerOrder;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    private static $columns = [
        'partnerOrder' => ['id', 'order_id', 'partner_id', 'sheba_collection', 'partner_collection', 'finance_collection',
            'closed_at', 'closed_and_paid_at', 'cancelled_at', 'created_by_name', 'created_at', 'updated_at'],
        'partner' => ['id', 'name', 'mobile', 'package_id'],
        'subscription' => ['id', 'name'],
        'order' => ['id', 'customer_id', 'location_id', 'voucher_id', 'affiliation_id', 'sales_channel', 'portal_name',
            'delivery_name', 'delivery_mobile', 'created_at', 'delivery_address_id', 'info_call_id'],
        'customer' => ['id', 'profile_id', 'is_vip', 'created_at'],
        'profile' => ['id', 'name', 'mobile'],
        'location' => ['id', 'name', 'geo_informations'],
        'voucher' => ['id', 'code'],
        'tags' => ['id', 'name'],
        'affiliation' => ['id', 'affiliate_id'],
        'affiliate' => ['id', 'profile_id'],
        'payments' => ['id', 'partner_order_id', 'amount', 'collected_by', 'transaction_type'],
        'jobs' => ['id', 'partner_order_id', 'category_id', 'service_id', 'resource_id', 'crm_id',
            'job_additional_info', 'service_quantity', 'service_type', 'schedule_date', 'preferred_time',
            'service_unit_price', 'commission_rate', 'delivery_charge', 'vat', 'discount',
            'sheba_contribution', 'partner_contribution', 'discount_percentage', 'status', 'material_commission_rate'],
        'usedMaterials' => ['id', 'job_id', 'material_price'],
        'crm' => ['id', 'name'],
        'review' => ['id', 'job_id', 'rating', 'review', 'created_at', 'created_by_name'],
        'jobServices' => ['id', 'job_id', 'service_id', 'name', 'quantity', 'min_price', 'unit_price', 'discount',
            'sheba_contribution', 'partner_contribution', 'discount_percentage'],
        'service' => ['id', 'name', 'category_id'],
        'category' => ['id', 'name', 'parent_id'],
        'complains' => ['id', 'job_id', 'accessor_id'],
        'resource' => ['id', 'profile_id'],
        'cancelRequest' => ['id', 'job_id', 'status'],
        'updateLogs' => ['id', 'job_id', 'log'],
        'cancelLog' => ['id', 'job_id', 'cancel_reason', 'cancel_reason_details', 'created_at'],
        'partnerChangeLog' => ['id', 'job_id', 'cancel_reason'],
        'statusChangeLogs' => ['id', 'job_id', 'to_status', 'from_status', 'portal_name', 'created_at', 'created_by_name'],
        'scheduleDueLog' => ['id', 'job_id'],
    ];

    public function build()
    {
        return $this->optimizedQuery();
    }

    private function normalQuery()
    {
        return PartnerOrder::with('partner.subscription', 'order.customer.profile',
            'order.location', 'order.voucher.tags', 'order.affiliation.affiliate.tags', 'payments',
            'jobs.usedMaterials', 'jobs.crm', 'jobs.review', 'jobs.category', 'jobs.jobServices',
            'jobs.complains', 'jobs.resource.profile', 'jobs.cancelLog', 'jobs.partnerChangeLog', 'jobs.statusChangeLogs');
    }

    private function optimizedQuery()
    {
        return PartnerOrder::select(self::$columns['partnerOrder'])->with([
            'partner' => function ($partner_query) {
                $partner_query->select(self::$columns['partner'])->with([
                    'subscription' => function ($subscription_query) {
                        $subscription_query->select(self::$columns['subscription']);
                    }
                ]);
            },
            'order' => function ($order_query) {
                $order_query->select(self::$columns['order'])->with([
                    'customer' => function ($customer_query) {
                        $customer_query->select(self::$columns['customer'])->with([
                            'profile' => function ($profile_query) {
                                $profile_query->select(self::$columns['profile']);
                            }
                        ]);
                    },
                    'location' => function ($location_query) {
                        $location_query->select(self::$columns['location']);
                    },
                    'voucher' => function ($voucher_query) {
                        $voucher_query->select(self::$columns['voucher'])->with([
                            'tags' => function ($tags_query) {
                                $tags_query->select(self::$columns['tags']);
                            }
                        ]);
                    },
                    'affiliation' => function ($affiliation_query) {
                        $affiliation_query->select(self::$columns['affiliation'])->with([
                            'affiliate' => function ($affiliate_query) {
                                $affiliate_query->select(self::$columns['affiliate'])->with([
                                    'tags' => function ($tags_query) {
                                        $tags_query->select(self::$columns['tags']);
                                    }
                                ]);
                            }
                        ]);
                    },
                ]);
            },
            'payments' => function ($payments_query) {
                $payments_query->select(self::$columns['payments']);
            },
            'jobs' => function ($jobs_query) {
                $jobs_query->select(self::$columns['jobs'])->with([
                    'usedMaterials' => function ($material_query) {
                        $material_query->select(self::$columns['usedMaterials']);
                    },
                    'crm' => function ($crm_query) {
                        $crm_query->select(self::$columns['crm']);
                    },
                    'review' => function ($review_query) {
                        $review_query->select(self::$columns['review']);
                    },
                    'jobServices' => function ($job_service_query) {
                        $job_service_query->select(self::$columns['jobServices']);
                    },
                    'service' => function ($service_query) {
                        $service_query->select(self::$columns['service'])->with([
                            'category' => function ($category_query) {
                                $category_query->select(self::$columns['category'])->with([
                                    'parent' => function ($parent_category_query) {
                                        $parent_category_query->select(self::$columns['category']);
                                    }
                                ]);
                            }
                        ]);
                    },
                    'category' => function ($category_query) {
                        $category_query->select(self::$columns['category'])->with([
                            'parent' => function ($parent_category_query) {
                                $parent_category_query->select(self::$columns['category']);
                            }
                        ]);
                    },
                    'complains' => function ($complain_query) {
                        $complain_query->select(self::$columns['complains']);
                    },
                    'resource' => function ($resource_query) {
                        $resource_query->select(self::$columns['resource'])->with([
                            'profile' => function ($profile_query) {
                                $profile_query->select(self::$columns['profile']);
                            }
                        ]);
                    },
                    'cancelRequest' => function ($cancel_request_query) {
                        $cancel_request_query->select(self::$columns['cancelRequest']);
                    },
                    'updateLogs' => function ($update_log_query) {
                        $update_log_query->select(self::$columns['updateLogs']);
                    },
                    'cancelLog' => function ($cancel_log_query) {
                        $cancel_log_query->select(self::$columns['cancelLog']);
                    },
                    'partnerChangeLog' => function ($parent_change_query) {
                        $parent_change_query->select(self::$columns['partnerChangeLog']);
                    },
                    'statusChangeLogs' => function ($status_log_query) {
                        $status_log_query->select(self::$columns['statusChangeLogs']);
                    },
                    'scheduleDueLog' => function ($cancel_log_query) {
                        $cancel_log_query->select(self::$columns['scheduleDueLog']);
                    }
                ]);
            }
        ]);
    }
}