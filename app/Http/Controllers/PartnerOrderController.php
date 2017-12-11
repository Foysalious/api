<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\PartnerJobRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
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
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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
            return api_response($request, null, 500);
        }
    }

    public function getBills($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order->load(['order', 'jobs' => function ($q) {
                $q->info()->with(['service', 'usedMaterials' => function ($q) {
                    $q->select('id', 'job_id', 'material_name', 'material_price');
                }]);
            }]);
            $partner_order->calculate();
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
            return api_response($request, null, 500);
        }
    }

    public function getLogs($partner, Request $request)
    {
        try {
            if ($request->has('filter')) {
                $filter = $request->filter;
                $logs = $request->partner_order->$filter->where('transaction_type', 'Debit');
                if (count($logs) == 0) {
                    return api_response($request, $logs, 404);
                }
                $logs->each(function ($item, $key) {
                    $item['amount'] = (double)$item->amount;
                    $item['collected_by'] = trim(explode('-', $item->created_by_name)[1]);
                    removeSelectedFieldsFromModel($item);
                });
                return api_response($request, $logs, 200, ['logs' => $logs]);
            }
            $all_logs = collect();
            foreach ($request->partner_order->jobs as $job) {
                $job_logs = (new JobLogs($job))->all();
                foreach ($job_logs as $key => $job_log) {
                    if ($key == 'price_change') {
                        $price_changes = $job_log;
                        foreach ($price_changes as $price_change) {
                            $collect = collect();
                            $collect->put('log', $price_change->log . ' from ' . $price_change->from . ' to ' . $price_change->to);
                            $collect->put('type', $key);
                            $collect->put('timestamp', $price_change->created_at->timestamp);
                            $collect->put('created_at', $price_change->created_at->format('Y-m-d'));
                            $all_logs->push($collect->toArray());
                        }
                    } elseif ($key == 'status_change') {
                        $status_changes = $job_log;
                        foreach ($status_changes as $status_change) {
                            $collect = collect();
                            $collect->put('log', 'Job status has changed from ' . $status_change->from_status . ' to ' . $status_change->to_status);
                            $collect->put('type', $key);
                            $collect->put('timestamp', $status_change->created_at->timestamp);
                            $collect->put('created_at', $status_change->created_at->format('Y-m-d'));
                            $all_logs->push($collect->toArray());
                        }
                    } else {
                        foreach ($job_log as $log) {
                            $collect = collect($log);
                            $collect->put('created_at', $log->created_at->toDateString());
                            $collect->put('timestamp', $log->created_at->timestamp);
                            $collect->put('type', $key);
                            $collect->forget('created_by_name');
                            $all_logs->push(($collect)->toArray());
                        }
                    }
                }
                $comments = $job->comments->where('is_visible', 1);
                foreach ($comments as $comment) {
                    $collect = collect();
                    $collect->put('log', explode('-', $comment->created_by_name)[1] . ' has commented');
                    $collect->put('comment', $comment->comment);
                    $collect->put('timestamp', $comment->created_at->timestamp);
                    $collect->put('created_at', $comment->created_at->format('Y-m-d'));
                    $collect->put('type', 'comment');
                    $all_logs->push($collect->toArray());
                }
            }
            $dates = $all_logs->groupBy('created_at')->sortBy(function ($item, $key) {
                return $key;
            });
            return api_response($request, $dates, 200, ['logs' => $dates]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function postComment($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order;
            $manager_resource = $request->manager_resource;
            $comment = (new CommentRepository('Job', $partner_order->jobs->pluck('id')->first(), $manager_resource))->store($request->comment, true);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
