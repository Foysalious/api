<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Repositories\CommentRepository;
use App\Repositories\JobServiceRepository;
use App\Repositories\PartnerJobRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Logs\OrderLogs;
use App\Sheba\Logs\PartnerOrderLogs;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Sheba\Logs\JobLogs;
use Validator;

class PartnerOrderController extends Controller
{
    private $partnerOrderRepository;
    private $partnerJobRepository;

    public function __construct()
    {
        $this->partnerOrderRepository = new PartnerOrderRepository();
        $this->partnerJobRepository = new PartnerJobRepository();
    }

    public function show($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'sometimes|bail|required|string',
                'filter' => 'sometimes|bail|required|string|in:ongoing,history'
            ]);
            $partner_order = $this->partnerOrderRepository->getOrderDetails($request);
            $partner_order['version'] = $partner_order->getVersion();
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function showV2($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'sometimes|bail|required|string',
                'filter' => 'sometimes|bail|required|string|in:ongoing,history'
            ]);
            $partner_order = $this->partnerOrderRepository->getOrderDetailsV2($request);
            $partner_order['version'] = 'v2';
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function newOrders($partner, Request $request)
    {
        try {
            $this->validate($request, ['sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc,schedule_date,schedule_date:asc,schedule_date:desc']);
            $orders = $this->partnerOrderRepository->getNewOrdersWithJobs($request);
            return count($orders) > 0 ? api_response($request, $orders, 200, ['orders' => $orders]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getOrders($partner, Request $request)
    {
        try {
            $this->validate($request, ['sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc', 'filter' => 'required|in:ongoing,history']);
            $partner_orders = $this->partnerOrderRepository->getOrders($request);
            return count($partner_orders) > 0 ? api_response($request, $partner_orders, 200, ['orders' => $partner_orders]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBillsV1($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order->load(['order', 'jobs' => function ($q) {
                $q->info()->with(['service', 'usedMaterials' => function ($q) {
                    $q->select('id', 'job_id', 'material_name', 'material_price');
                }]);
            }]);
            $partner_order->calculate(true);
            $jobs = (new ResourceJobRepository())->addJobInformationForAPI($partner_order->jobs->each(function ($item) use ($partner_order) {
                $item['partner_order'] = $partner_order;
            }));
            $partner_order['paid_amount'] = (double)$partner_order->paid;
            $partner_order['due_amount'] = (double)$partner_order->due;
            $partner_order['total'] = (double)$partner_order->totalPrice;
            $partner_order['sheba_fee'] = ((double)$partner_order->profit > 0) ? (double)$partner_order->profit : 0;
            $partner_order['total_cost_without_discount'] = (double)$partner_order->totalCostWithoutDiscount;
            $partner_order['total_partner_discount'] = (double)$partner_order->totalPartnerDiscount;
            $partner_order['total_cost'] = (double)$partner_order->totalCost;
            $partner_order['is_paid'] = ((double)$partner_order->due == 0) ? true : false;
            $partner_order['is_due'] = ((double)$partner_order->due > 0) ? true : false;
            $partner_order['is_closed'] = ($partner_order->closed_at != null) ? true : false;
            $partner_order['order_status'] = $partner_order->status;
            if ($partner_order['is_closed'] && $partner_order['is_due']) {
                $partner_order['overdue'] = $partner_order->closed_at->diffInDays(Carbon::now());
            } else {
                $partner_order['overdue'] = null;
            }
            removeRelationsFromModel($partner_order);
            removeSelectedFieldsFromModel($partner_order);
            $partner_order['jobs'] = $jobs->each(function ($item) {
                removeRelationsFromModel($item);
            });
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBillsV2($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order;
            $partner_order->calculate(true);
            foreach ($partner_order->jobs as $job) {
                if (count($job->jobServices) == 0) {
                    $services = array();
                    array_push($services, array(
                        'name' => $job->service_name,
                        'quantity' => (double)$job->quantity, 'price' => (double)$job->servicePrice));
                } else {
                    $services = array();
                    foreach ($job->jobServices as $jobService) {
                        array_push($services, array(
                            'name' => $jobService->service ? $jobService->service->name : null,
                            'quantity' => (double)$jobService->quantity,
                            'sheba_contribution' => (double)$jobService->sheba_contribution,
                            'partner_contribution' => (double)$jobService->partner_contribution,
                            'price' => (double)$jobService->unit_price * (double)$jobService->quantity));
                    }
                }
            }
            $partner_order = array(
                'id' => $partner_order->id,
                'total_material_price' => (double)$partner_order->totalMaterialPrice,
                'total_price' => (double)$partner_order->totalPrice,
                'discount' => (double)$partner_order->totalDiscount,
                'paid' => (double)$partner_order->paid,
                'due' => (double)$partner_order->due,
                'invoice' => $partner_order->invoice,
                'sheba_commission' => (double)$partner_order->profit,
                'partner_commission' => (double)$partner_order->totalCost,
                'service' => $services,
                'is_paid' => (double)$partner_order->due == 0,
                'is_due' => (double)$partner_order->due > 0,
                'is_closed' => $partner_order->closed_at != null
            );
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getLogs($partner, Request $request)
    {
        try {
            $all_logs = collect();
            $this->formatLogs((new PartnerOrderLogs($request->partner_order))->all(), $all_logs);
            $this->formatLogs((new OrderLogs($request->partner_order->order))->all(), $all_logs);
            foreach ($request->partner_order->jobs as $job) {
                $job_logs = (new JobLogs($job))->all();
                $this->formatLogs($job_logs, $all_logs);
            }
            $dates = $all_logs->groupBy('created_at')->sortByDesc(function ($item, $key) {
                return $key;
            })->map(function ($item, $key) {
                return ($item->sortByDesc('timestamp'))->values()->all();
            });
            return api_response($request, $dates, 200, ['logs' => $dates]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPayments($partner, Request $request)
    {
        try {
            $logs = $request->partner_order->payments->where('transaction_type', 'Debit');
            if (count($logs) != 0) {
                $logs->each(function ($item, $key) {
                    $name = explode('-', $item->created_by_name);
                    $item['collected_by'] = $item->created_by_name;
                    if (trim($name[0]) == 'Resource') {
                        $resource = Resource::find($item->created_by);
                        $item['picture_of_collected_by'] = $resource != null ? $resource->profile->pro_pic : null;
                    } elseif (trim($name[0]) == 'Customer') {
                        $item['picture_of_collected_by'] = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/manager_app/customer.jpg';
                    } else {
                        $item['collected_by'] = 'Sheba.xyz';
                        $item['picture_of_collected_by'] = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/manager_app/sheba.jpg';
                    }
                    removeSelectedFieldsFromModel($item);
                });
                return api_response($request, $logs, 200, ['logs' => $logs]);
            }
            return api_response($request, $logs, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function postComment($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order;
            $manager_resource = $request->manager_resource;
            $comment = (new CommentRepository('Job', $partner_order->jobs->pluck('id')->first(), $manager_resource))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $job_logs
     * @param $all_logs
     */
    private function formatLogs($job_logs, $all_logs)
    {
        foreach ($job_logs as $key => $job_log) {
            foreach ($job_log as $log) {
                $collect = collect($log);
                $collect->put('created_at', $log->created_at->toDateString());
                $collect->put('timestamp', $log->created_at->timestamp);
                $collect->put('type', $key);
                $all_logs->push($collect);
            }
        }
    }

    public function addService($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string',
                'remember_token' => 'required|string',
                'partner' => 'required'
            ]);
            $partner_order = $request->partner_order;
            $manager_resource = $request->manager_resource;
            $job = $partner_order->jobs->whereIn('status', array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process']))->first();
            if ($job == null) return api_response($request, null, 403, ['message' => "No valid job exists"]);
            $partner_list = new PartnerList(json_decode($request->services), $job->schedule_date, $job->preferred_time_start . '-' . $job->preferred_time_end, $partner_order->order->location_id);
            $partners = collect();
            $partners->push($request->partner->load(['services' => function ($q) use ($partner_list) {
                $q->whereIn('service_id', $partner_list->selected_services->pluck('id')->unique());
            }]));
            $partner_list->partners = $partners;
            $partner_list->addPricing();
            $partner = $partner_list->partners->first();
            $jobService_repo = new JobServiceRepository();
            $job_services = $jobService_repo->createJobService($partner->services, $partner_list->selected_services, ['created_by' => $manager_resource->id, 'created_by_name' => $manager_resource->profile->name]);
            if (!$jobService_repo->existInJob($job, $job_services)) {
                $job->jobServices()->saveMany($job_services);
                return api_response($request, null, 200);
            } else {
                return api_response($request, null, 403, ['message' => 'You can not add service that is already added!']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function collectMoney($partner, Request $request)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric']);
            $partner_order = $request->partner_order;
            $request->merge(['resource' => $request->manager_resource]);
            $response = (new ResourceJobRepository())->collectMoney($partner_order, $request);
            if ($response) {
                if ($response->code == 200) {
                    return api_response($request, $response, 200, ['message' => $request->amount . 'Tk have been successfully collected.']);
                } else {
                    return api_response($request, $response, $response->code);
                }
            }
            return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
