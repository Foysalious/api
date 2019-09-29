<?php namespace Sheba\Reports;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class JobReportData extends ReportData
{
    /**
     * @param Request $request
     * @return Collection
     */
    public function get(Request $request)
    {
        $jobs = $this->makeSelectQuery2();
        $jobs = $this->notLifetimeQuery($jobs, $request->all());
        $jobs = $this->filterSchedule($jobs, $request->all());
        $jobs = $this->filterServed($jobs, $request->all());
        $jobs = $this->filterCancelled($jobs, $request->all());
        $jobs = $this->filterRate($jobs, $request->all());
        $jobs = $jobs->get()->map(function (Job $job) {
            return $job->calculate();
        });
        
        return $jobs;
    }

    private function filterSchedule($query, $request_data)
    {
        if (empty($request_data['schedule_start_date']) || empty($request_data['schedule_end_date'])) return $query;

        $schedule_start_date = $request_data['schedule_start_date'] . ' 00:00:00';
        $schedule_end_date = $request_data['schedule_end_date'] . ' 23:59:59';
        $query = $query->whereBetween('schedule_date', [$schedule_start_date, $schedule_end_date]);

        return $query;
    }

    private function filterServed($query, $request_data)
    {
        if (empty($request_data['served_start_date']) || empty($request_data['served_end_date'])) return $query;

        $served_start_date = $request_data['served_start_date'] . ' 00:00:00';
        $served_end_date = $request_data['served_end_date'] . ' 23:59:59';
        $query = $query->where('status', 'Served')->whereBetween('delivered_date', [$served_start_date, $served_end_date]);

        return $query;
    }

    private function filterCancelled($query, $request_data)
    {
        if (empty($request_data['cancel_start_date']) || empty($request_data['cancel_end_date'])) return $query;

        $cancel_start_date = $request_data['cancel_start_date'] . ' 00:00:00';
        $cancel_end_date = $request_data['cancel_end_date'] . ' 23:59:59';
        $query = $query->where('status', 'Cancelled')->whereHas('partnerOrder', function ($q) use ($cancel_start_date, $cancel_end_date) {
            return $q->whereBetween('cancelled_at', [$cancel_start_date, $cancel_end_date]);
        });

        return $query;
    }

    private function filterRate($query, $request_data)
    {
        if (empty($request_data['rated_start_date']) || empty($request_data['rated_end_date'])) return $query;

        $rated_start_date = $request_data['rated_start_date'] . ' 00:00:00';
        $rated_end_date = $request_data['rated_end_date'] . ' 23:59:59';
        $query = $query->whereHas('review', function ($q) use ($rated_start_date, $rated_end_date) {
            $q->whereBetween('created_at', [$rated_start_date, $rated_end_date]);
        });

        return $query;
    }

    private function makeSelectQuery()
    {
        return Job::with('partnerOrder.order.customer.profile', 'jobServices', 'usedMaterials',
            'partnerOrder.order.location', 'partnerOrder.partner',  'service.category.parent', 'category.parent',
            'crm', 'complains', 'review', 'cancelRequest', 'resource',
            'statusChangeLogs', 'cancelLog', 'partnerChangeLog', 'updateLogs');
    }

    private function makeSelectQuery2()
    {
        return Job::select($this->getSelectColumnsOfJob())->with([
            'partnerOrder' => function($partner_order_query) {
                $partner_order_query->select($this->getSelectColumnsOfPartnerOrder())->with([
                    'order' => function($order_query) {
                        $order_query->select($this->getSelectColumnsOfOrder())->with([
                            'customer' => function ($customer_query) {
                                $customer_query->select($this->getSelectColumnsOfCustomer())->with([
                                    'profile' => function ($profile_query) {
                                        $profile_query->select($this->getSelectColumnsOfProfile());
                                    }
                                ]);
                            },
                            'location' => function($location_query) {
                                $location_query->select($this->getSelectColumnsOfLocation());
                            }
                        ]);
                    },
                    'partner' => function($partner_query) {
                        $partner_query->select($this->getSelectColumnsOfPartner());
                    }
                ]);
            },
            'jobServices' => function($job_service_query) {
                $job_service_query->select($this->getSelectColumnsOfJobServices());
            },
            'resource' => function($resource_query) {
                $resource_query->select($this->getSelectColumnsOfResource());
            },
            'usedMaterials' => function($material_query) {
                $material_query->select($this->getSelectColumnsOfMaterial());
            },
            'service' => function($service_query) {
                $service_query->select($this->getSelectColumnsOfService())->with([
                    'category' => function($category_query) {
                        $category_query->select($this->getSelectColumnsOfCategory())->with([
                            'parent' => function($parent_category_query) {
                                $parent_category_query->select($this->getSelectColumnsOfCategory());
                            }
                        ]);
                    }
                ]);
            },
            'category' => function($category_query) {
                $category_query->select($this->getSelectColumnsOfCategory())->with([
                    'parent' => function($parent_category_query) {
                        $parent_category_query->select($this->getSelectColumnsOfCategory());
                    }
                ]);
            },
            'crm' => function($crm_query) {
                $crm_query->select($this->getSelectColumnsOfUser());
            },
            'complains' => function($complain_query) {
                $complain_query->select($this->getSelectColumnsOfComplain());
            },
            'review' => function($review_query) {
                $review_query->select($this->getSelectColumnsOfReview());
            },
            'statusChangeLogs' => function($status_log_query) {
                $status_log_query->select($this->getSelectColumnsOfStatusLog());
            },
            'cancelLog' => function($cancel_log_query) {
                $cancel_log_query->select($this->getSelectColumnsOfCancelLog());
            },
            'partnerChangeLog' => function($parent_change_query) {
                $parent_change_query->select($this->getSelectColumnsOfPartnerChangeLog());
            },
            'updateLogs' => function($update_log_query) {
                $update_log_query->select($this->getSelectColumnsOfUpdateLog());
            },
            'scheduleDueLog' => function($schedule_due_log_query) {
                $schedule_due_log_query->select($this->getSelectColumnsOfScheduleDueLog());
            },
            'cancelRequest' => function($cancel_request_query) {
                $cancel_request_query->select($this->getSelectColumnsOfCancelRequest());
            }
        ]);
    }

    private function getSelectColumnsOfJob()
    {
        return ['id', 'partner_order_id', 'category_id', 'service_id', 'job_additional_info', 'service_quantity',
            'service_type', 'resource_id', 'crm_id', 'schedule_date', 'preferred_time', 'service_unit_price',
            'commission_rate', 'delivery_charge', 'vat', 'discount', 'sheba_contribution', 'partner_contribution',
            'discount_percentage', 'status', 'delivered_date', 'warranty', 'is_recurring',
            'created_by_name', 'created_at', 'updated_at'];
    }

    private function getSelectColumnsOfPartnerOrder()
    {
        return ['id', 'order_id', 'partner_id', 'cancelled_at'];
    }

    private function getSelectColumnsOfOrder()
    {
        return ['id', 'customer_id', 'location_id', 'delivery_name', 'sales_channel', 'reference'];
    }

    private function getSelectColumnsOfJobServices()
    {
        return ['id', 'job_id', 'service_id', 'name', 'quantity', 'min_price', 'unit_price', 'discount',
            'sheba_contribution', 'partner_contribution', 'discount_percentage'];
    }

    private function getSelectColumnsOfCustomer()
    {
        return ['id', 'profile_id', 'is_vip'];
    }

    private function getSelectColumnsOfProfile()
    {
        return ['id', 'name', 'mobile'];
    }

    private function getSelectColumnsOfPartner()
    {
        return ['id', 'name'];
    }

    private function getSelectColumnsOfLocation()
    {
        return ['id', 'name'];
    }

    private function getSelectColumnsOfResource()
    {
        return ['id', 'profile_id'];
    }

    private function getSelectColumnsOfMaterial()
    {
        return ['id', 'job_id', 'material_price'];
    }

    private function getSelectColumnsOfService()
    {
        return ['id', 'name', 'category_id'];
    }

    private function getSelectColumnsOfCategory()
    {
        return ['id', 'name', 'parent_id'];
    }

    private function getSelectColumnsOfUser()
    {
        return ['id', 'name'];
    }

    private function getSelectColumnsOfComplain()
    {
        return ['id', 'job_id'];
    }

    private function getSelectColumnsOfReview()
    {
        return ['id', 'job_id', 'rating', 'created_at', 'created_by_name'];
    }

    private function getSelectColumnsOfStatusLog()
    {
        return ['id', 'job_id', 'to_status', 'created_at'];
    }

    private function getSelectColumnsOfCancelLog()
    {
        return ['id', 'job_id', 'created_at'];
    }

    private function getSelectColumnsOfPartnerChangeLog()
    {
        return ['id', 'job_id', 'from_status', 'created_at'];
    }

    private function getSelectColumnsOfCancelRequest()
    {
        return ['id', 'job_id', 'created_at'];
    }

    private function getSelectColumnsOfUpdateLog()
    {
        return ['id', 'job_id', 'log'];
    }

    private function getSelectColumnsOfScheduleDueLog()
    {
        return ['id', 'job_id'];
    }
}