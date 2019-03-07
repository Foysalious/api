<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Sheba\Logs\Customer\JobLogs;

class OrderType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Order'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'order_id' => ['type' => Type::int()],
            'customer' => ['type' => GraphQL::type('Customer')],
            'category' => ['type' => GraphQL::type('Category')],
            'partner' => ['type' => GraphQL::type('Partner')],
            'jobs' => ['type' => Type::listOf(GraphQL::type('Job'))],
            'code' => ['type' => Type::string()],
            'address' => ['type' => Type::string()],
            'status' => ['type' => Type::string()],
            'readable_status' => ['type' => Type::string()],
            'schedule_date' => ['type' => Type::string()],
            'process_date' => ['type' => Type::string()],
            'served_date' => ['type' => Type::string()],
            'contact_number' => ['type' => Type::string()],
            'can_call_expert' => ['type' => Type::boolean()],
            'schedule_date_timestamp' => ['type' => Type::int()],
            'subscription_order_id' => ['type' => Type::int()],
            'schedule_time' => ['type' => Type::string()],
            'location' => ['type' => GraphQL::type('Location')],
            'original_price' => ['type' => Type::float()],
            'discounted_price' => ['type' => Type::float()],
            'total_material_price' => ['type' => Type::float()],
            'total_discount' => ['type' => Type::float()],
            'paid' => ['type' => Type::float()],
            'payment_method' => ['type' => Type::string()],
            'due' => ['type' => Type::float()],
            'delivery_address' => ['type' => Type::string()],
            'delivery_name' => ['type' => Type::string()],
            'delivery_mobile' => ['type' => Type::string()],
            'version' => ['type' => Type::string()],
            'closed_and_paid_at' => ['type' => Type::string()],
            'closed_and_paid_at_timestamp' => ['type' => Type::int()],
            'completed_at' => ['type' => Type::string()],
            'completed_at_timestamp' => ['type' => Type::string()],
            'invoice' => ['type' => Type::string()],
            'message' => ['type' => GraphQL::type('OrderMessage')]
        ];
    }

    protected function resolveCustomerField($root)
    {
        return $root->order->customer;
    }

    protected function resolveOrderIdField($root)
    {
        return $root->order->id;
    }

    protected function resolveCategoryField($root)
    {
        return count($root->jobs) > 0 ? $root->jobs[0]->category : null;
    }

    protected function resolveCodeField($root)
    {
        return $root->order->code();
    }

    protected function resolvePaidField($root)
    {
        if (!isset($root['paid'])) {
            $root->calculate(true);
        }
        return (float)$root->paid;
    }

    protected function resolvePaymentMethodField($root)
    {
        if ($root->payment_method == 'cash-on-delivery' || $root->payment_method == 'Cash On Delivery') return 'cod';
        return strtolower($root->payment_method);
    }

    protected function resolveDueField($root)
    {
        if (!isset($root['due'])) {
            $root->calculate(true);
        }
        return (float)$root->due;
    }

    protected function resolveDiscountedPriceField($root)
    {
        if (!isset($root['totalPrice'])) {
            $root->calculate(true);
        }
        return (float)$root->totalPrice;
    }

    protected function resolveTotalMaterialPriceField($root)
    {
        if (!isset($root['totalMaterialPrice'])) {
            $root->calculate(true);
        }
        return (float)$root->totalMaterialPrice;
    }

    protected function resolveTotalDiscountField($root)
    {
        if (!isset($root['totalDiscount'])) {
            $root->calculate(true);
        }
        return (float)$root->totalDiscount;
    }

    protected function resolveOriginalPriceField($root)
    {
        if (!isset($root['jobPrices'])) {
            $root->calculate(true);
        }
        return (double)$root->jobPrices;
    }

    protected function resolveAddressField($root)
    {
        return $root->order->delivery_address;
    }

    protected function resolveLocationField($root)
    {
        return $root->order->location;
    }

    protected function resolveStatusField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return $not_cancelled_job ? $not_cancelled_job->status : null;
    }

    protected function resolveReadableStatusField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return $not_cancelled_job ? constants('JOB_STATUSES_SHOW')[$not_cancelled_job->status]['customer'] : null;
    }

    protected function resolveCanCallExpertField($root, $args)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return $not_cancelled_job->canCallExpert();
    }

    protected function resolveContactNumberField($root, $args)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return $not_cancelled_job->canCallExpert() ? ($not_cancelled_job->resource ? $not_cancelled_job->resource->profile->mobile : null) : $root->partner->getManagerMobile();
    }

    protected function resolveMessageField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return $not_cancelled_job ? (new JobLogs($not_cancelled_job))->getOrderMessage() : null;
    }

    protected function resolveScheduleDateField($root)
    {
        return $root->jobs[0]->schedule_date;
    }

    protected function resolveProcessDateField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->with('statusChangeLogs')->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        if ($not_cancelled_job && $not_cancelled_job->statusChangeLogs) {
            $process_log = $not_cancelled_job->statusChangeLogs->where('to_status', constants('JOB_STATUSES')['Process'])->first();
            if ($process_log) return $process_log->created_at->format('Y-m-d H:i:s');
        }
        return null;
    }

    protected function resolveServedDateField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        if ($not_cancelled_job && $not_cancelled_job->delivered_date) {
            return $not_cancelled_job->delivered_date->format('Y-m-d H:i:s');
        }
        return null;
    }

    protected function resolveScheduleDateTimestampField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return Carbon::parse($not_cancelled_job->schedule_date)->timestamp;
    }

    protected function resolveScheduleTimeField($root)
    {
        if (!$root->cancelled_at && !$root->jobs) {
            $root->load(['jobs' => function ($q) {
                $q->where('status', '<>', 'Cancelled');
            }]);
        }
        $not_cancelled_job = $root->jobs->first();
        return humanReadableShebaTime($not_cancelled_job->preferred_time);
    }

    protected function resolveDeliveryAddressField($root)
    {
        return $root->order->deliveryAddress->address;
    }

    protected function resolveDeliveryNameField($root)
    {
        return $root->order->delivery_name;
    }

    protected function resolveDeliveryMobileField($root)
    {
        return $root->order->delivery_mobile;
    }

    protected function resolveClosedAndPaidAtField($root)
    {
        return $root->closed_and_paid_at ? $root->closed_and_paid_at->format('Y-m-d') : null;
    }

    protected function resolveClosedAndPaidAtTimestampField($root)
    {
        return $root->closed_and_paid_at ? $root->closed_and_paid_at->timestamp : null;
    }

    protected function resolveCompletedAtField($root)
    {
        return $root->completed_at ? $root->completed_at->format('Y-m-d') : null;
    }

    protected function resolveCompletedAtTimestampField($root)
    {
        return $root->completed_at ? $root->completed_at->timestamp : null;
    }

    protected function resolveVersionField($root)
    {
        return $root->getVersion();
    }

    protected function resolveInvoiceField($root)
    {
        return $root->invoice;
    }

    protected function resolveSubscriptionOrderField($root)
    {
        return $root->order->subscription_order_id;
    }
}