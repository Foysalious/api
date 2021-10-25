<?php namespace App\Http\Controllers;

use App\Models\Resource;
use App\Repositories\CommentRepository;
use App\Repositories\JobServiceRepository;
use App\Repositories\PartnerJobRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\ResourceJobRepository;
use App\Sheba\Checkout\PartnerList;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Helpers\TimeFrame;
use Sheba\Jobs\Discount;
use Sheba\Jobs\JobStatuses;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Repository\OrderRepository;
use Sheba\Logs\OrderLogs;
use App\Sheba\Logs\PartnerOrderLogs;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Logs\JobLogs;
use Sheba\ModificationFields;
use Sheba\Resource\Jobs\Collection\CollectMoney;
use Sheba\Resource\Jobs\Service\ServiceUpdateRequest;
use Sheba\Services\FormatServices;
use Sheba\UserAgentInformation;
use Throwable;
use Validator;

class PartnerOrderController extends Controller
{
    use ModificationFields;

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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function newOrders($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc,schedule_date,schedule_date:asc,schedule_date:desc',
                'getCount' => 'sometimes|required|numeric|in:1'
            ]);
            if ($request->has('getCount')) {
                $partner = $request->partner->load(['jobs' => function ($q) {
                    $q->status([JobStatuses::PENDING, JobStatuses::NOT_RESPONDED])->select('jobs.id', 'jobs.partner_order_id')
                        ->whereDoesntHave('cancelRequests', function ($q) {
                            $q->select('id', 'job_id', 'status')->where('status', 'Pending');
                        })->with(['partnerOrder' => function ($q) {
                            $q->select('id', 'order_id')->with(['order' => function ($q) {
                                $q->select('id', 'subscription_order_id');
                            }]);
                        }]);
                }]);
                $time_frame = new TimeFrame();
                $start_end_date = $time_frame->forTodayAndYesterday();
                $order_request_count = PartnerOrderRequest::openRequest()->whereDoesntHave('partnerOrder', function ($q) {
                    $q->new();
                })->where('partner_id', $partner->id)->whereBetween('created_at', $start_end_date->getArray())->count();
                $total_new_orders = $partner->jobs->pluck('partnerOrder')->unique()->pluck('order')
                        ->groupBy('subscription_order_id')
                        ->map(function ($order, $key) {
                            return !empty($key) ? 1 : $order->count();
                        })->sum() + $order_request_count;
                return api_response($request, $total_new_orders, 200, ['total_new_orders' => $total_new_orders]);
            }
            $orders = $this->partnerOrderRepository->getNewOrdersWithJobs($request);
            return count($orders) > 0 ? api_response($request, $orders, 200, ['orders' => $orders]) : api_response($request, null, 404);
            /*$category_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
            $final = [];
            foreach ($orders as $order) {
                if (in_array($order['category_id'], $category_ids)) continue;
                array_push($final, $order);
            }
            return count($final) > 0 ? api_response($request, $final, 200, ['orders' => $final]) : api_response($request, null, 404);*/
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getOrders($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc',
                'filter' => 'required|in:ongoing,history',
                'for' => 'sometimes|required|string|in:eshop'
            ]);
            $partner_orders = $this->partnerOrderRepository->getOrders($request);
            return count($partner_orders) > 0 ? api_response($request, $partner_orders, 200, ['orders' => $partner_orders]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
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
            $partner_order['is_on_premise'] = 1;
            removeRelationsAndFields($partner_order);
            $partner_order['jobs'] = $jobs->each(function ($item) {
                removeRelationsAndFields($item);
                array_forget($item, 'partner_order');
            });
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBillsV2($partner, Request $request, FormatServices $formatServices)
    {
        try {
            $partner_order = $request->partner_order;
            $partner_order->calculate(true);
            foreach ($partner_order->jobs as $job) {
                $services = [];
                $service_list = [];
                if (count($job->jobServices) == 0) {
                    array_push($services, [
                        'name' => $job->service_name,
                        'quantity' => (double)$job->quantity,
                        'unit' => $job->service->unit,
                        'price' => (double)$job->servicePrice
                    ]);
                    array_push($service_list, [
                        'name' => $job->service_name,
                        'service_group' => [],
                        'unit' => $job->service->unit,
                        'quantity' => (double)$job->service_quantity,
                        'price' => (double)$job->servicePrice
                    ]);
                } else {
                    $service_list = $formatServices->setJob($job)->formatServices();
                    foreach ($job->jobServices as $job_service) {
                        array_push($services, [
                            'name' => $job_service->service ? $job_service->service->name : null,
                            'quantity' => (double)$job_service->quantity,
                            'unit' => $job_service->service ? $job_service->service->unit : null,
                            'discount' => (double)$job_service->discount,
                            'sheba_contribution' => (double)$job_service->sheba_contribution,
                            'partner_contribution' => (double)$job_service->partner_contribution,
                            'price' => (double)$job_service->unit_price * (double)$job_service->quantity
                        ]);
                    }
                }
            }
            $partner_order = [
                'id' => $partner_order->id,
                'total_material_price' => (double)$partner_order->totalMaterialPrice,
                'total_price' => (double)$partner_order->totalPrice + $partner_order->totalLogisticCharge,
                'paid' => (double)$partner_order->paidWithLogistic,
                'due' => (double)$partner_order->dueWithLogistic,
                'vat' => (double)$partner_order->vat,
                'vat_percentage' => config('sheba.category_vat_in_percentage'),
                'is_vat_applicable' => $job->category->is_vat_applicable,
                'invoice' => $partner_order->invoice,
                'sheba_commission' => ramp((double)$partner_order->profit),
                'partner_commission' => (double)$partner_order->totalCost,
                'service' => $services,
                'service_list' => $service_list,
                'is_paid' => (double)$partner_order->due == 0,
                'is_due' => (double)$partner_order->due > 0,
                'is_closed' => $partner_order->closed_at != null,
                'total_bill' => (double)$partner_order->totalServicePrice,
                'discount' => (double)$partner_order->totalDiscount,
                'total_sheba_discount_amount' => (double)$partner_order->totalShebaDiscount,
                'total_partner_discount_amount' => (double)$partner_order->totalPartnerDiscount,
                'service_discount' => (double)($partner_order->totalDiscount - $partner_order->totalDeliveryDiscount),
                'total_sheba_service_discount_amount' => (double)($partner_order->totalShebaDiscount - $partner_order->totalDeliveryDiscountShebaContribution),
                'total_partner_service_discount_amount' => (double)($partner_order->totalPartnerDiscount - $partner_order->totalDeliveryDiscountPartnerContribution),
                'delivery_charge' => (double)$partner_order->deliveryCharge + $partner_order->totalLogisticCharge,
                'delivery_discount' => (double)$partner_order->totalDeliveryDiscount,
                'sheba_delivery_discount_amount' => (double)$partner_order->totalDeliveryDiscountShebaContribution,
                'partner_delivery_discount_amount' => (double)$partner_order->totalDeliveryDiscountPartnerContribution,
                'is_logistic' => $partner_order->order->isLogisticOrder(),
                'is_ready_to_pick' => $partner_order->order->isReadyToPick()
            ];
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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

    public function addService($partner, Request $request, ServiceUpdateRequest $updateRequest, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, [
            'services' => 'required|string',
            'remember_token' => 'required|string',
            'partner' => 'required'
        ]);
        $partner_order = $request->partner_order;
        $this->setModifier($request->manager_resource);
        if ($partner_order->partner_id !== (int)$partner) return api_response($request, null, 403, ["message" => "You're not authorized to access this job."]);
        $user_agent_information->setRequest($request);
        $services = json_decode($request->services, 1);
        if (count($services) > 0) $updateRequest->setServices($services);
        $job = $partner_order->getActiveJob();
        if (!$job) return api_response($request, null, 404);
        $response = $updateRequest->setJob($job)->setUserAgentInformation($user_agent_information)->update();
        return api_response($request, null, $response->getCode(), ['message' => $response->getMessage()]);
    }

    public function collectMoney($partner, Request $request, CollectMoney $collect_money, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, ['amount' => 'required|numeric']);
        $user_agent_information->setRequest($request);
        $collect_money->setResource($request->manager_resource)->setPartnerOrder($request->partner_order)->setUserAgentInformation($user_agent_information)
            ->setCollectionAmount($request->amount);
        $response = $collect_money->collect();
        return api_response($request, $response, $response->getCode(), ['message' => $response->getMessage()]);
    }

    /**
     * @param $partner
     * @param $order_id
     * @param $logistic_order_id
     * @param Request $request
     * @param OrderRepository $order_repository
     * @return JsonResponse
     */
    public function retryRiderSearch($partner, $order_id, $logistic_order_id, Request $request, OrderRepository $order_repository)
    {
        try {
            $order_repository->retryRiderSearch($logistic_order_id);
            return api_response($request, null, 200, ['message' => 'Order search restarted']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
