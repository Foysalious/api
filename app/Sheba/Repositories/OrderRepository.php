<?php namespace Sheba\Repositories;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;

class OrderRepository extends BaseRepository
{

    /**
     * @param TimeFrame $time_frame
     * @return mixed
     */
    public function getAcquisitionData(TimeFrame $time_frame)
    {
        /*Order::with('customer.profile', 'location', 'jobs')
            ->whereBetween('created_at', $time_frame->getArray())->get();*/

        return Order::with([
            'customer' => function ($q) {
                $q->select('customers.id', 'customers.created_at', 'profile_id')->with(['profile' => function ($q1) {
                    $q1->select('mobile');
                }]);
            },
            'jobs' => function ($q2) {
                $q2->select('jobs.id');
            }
        ])->select('customer_id', 'location_id', 'sales_channel', 'orders.created_at', 'orders.id')
            ->whereBetween('created_at', $time_frame->getArray())
            ->get();
    }

    public function countUniqueCustomerFromOrdersOf(TimeFrame $time_frame)
    {
        return Order::distinct()->whereBetween('created_at', $time_frame->getArray())->get(['customer_id'])->count();
    }

    public function countTodayUniqueCustomer()
    {
        $time_frame = (new TimeFrame())->forADay(Carbon::today());
        return $this->countUniqueCustomerFromOrdersOf($time_frame);
    }

    public function countCreatedOrdersOf(Carbon $date)
    {
        return Order::whereDate('created_at', '=', $date->toDateString())->count();
    }

    public function countTodayCreatedOrders()
    {
        return $this->countCreatedOrdersOf(Carbon::today());
    }

    public function getCustomersFirstOrder(Collection $customers)
    {
        return Order::select('customer_id', 'id', 'created_at')
            ->whereIn('customer_id', $customers->toArray())
            ->groupBy('customer_id')
            ->get()
            ->groupBy('customer_id')
            ->map(function (Collection $orders) {
                return $orders->first();
            });
    }

    public function getRetentionData(TimeFrame $time_frame)
    {
        return Order::with([
            'customer' => function ($q) {
                $q->select('customers.id', 'customers.created_at');
            },
            'partnerOrders' => function ($q2) {
                $q2->select('partner_orders.id', 'partner_orders.order_id', 'partner_orders.cancelled_at')->with([
                    'jobs' => function ($q3) {
                        $q3->select('jobs.id', 'jobs.status', 'jobs.partner_order_id', 'jobs.service_unit_price', 'jobs.service_quantity', 'jobs.discount')->with([
                            'jobServices' => function ($q4) {
                                $q4->select('job_service.job_id', 'job_service.unit_price', 'job_service.quantity', 'job_service.discount');
                            },
                            'usedMaterials' => function ($q5) {
                                $q5->select('job_material.job_id', 'job_material.material_price');
                            }
                        ]);
                    }
                ]);
            }
        ])->select('customer_id', 'location_id', 'sales_channel', 'orders.created_at', 'orders.id')
            ->whereBetween('created_at', $time_frame->getArray())
            ->get();
    }
}