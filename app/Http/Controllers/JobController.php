<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerFavorite;
use App\Models\Job;
use App\Models\PartnerOrder;
use Illuminate\Support\Facades\App;
use Sheba\Authentication\AuthUser;
use Sheba\Customer\Jobs\Reschedule\Reschedule;
use Sheba\Dal\JobCancelReason\JobCancelReason;
use Sheba\Dal\LocationService\LocationService;
use App\Models\Payable;
use App\Models\Payment;
use App\Sheba\UserRequestInformation;
use App\Transformers\ServiceV2DeliveryChargeTransformer;
use App\Transformers\ServiceV2MinimalTransformer;
use App\Transformers\ServiceV2Transformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\CancelRequest\CancelRequestStatuses;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\Payable\Types;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Jobs\JobStatuses;
use Sheba\Location\FromGeo;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Logistics\Repository\OrderRepository;
use Sheba\Logs\Customer\JobLogs;
use Sheba\Order\Policy\Orderable;
use Sheba\Order\Policy\PreviousOrder;
use Sheba\PartnerOrder\InvoiceHandler;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\ShebaPaymentValidator;
use Sheba\Services\FormatServices;
use Sheba\UserAgentInformation;
use Sheba\Dal\PartnerOrderPayment;
use Throwable;

class JobController extends Controller
{
    private $jobStatusesShow;
    private $jobStatuses;

    public function __construct()
    {
        $this->jobStatusesShow = config('constants.JOB_STATUSES_SHOW');
        $this->jobStatuses = config('constants.JOB_STATUSES');
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'sometimes|string|in:ongoing,history'
            ]);
            $filter = $request->has('filter') ? $request->filter : null;
            $customer = $request->customer->load(['orders' => function ($q) use ($filter) {
                $q->with(['partnerOrders' => function ($q) use ($filter) {
                    if ($filter) {
                        $q->$filter();
                    }
                    $q->with(['partner', 'jobs' => function ($q) {
                        $q->with(['resource.profile', 'category', 'review']);
                    }]);
                }]);
            }]);
            $all_jobs = $this->getJobOfOrders($customer->orders->filter(function ($order) {
                return $order->partnerOrders->count() > 0;
            }))->sortByDesc('created_at');
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }


    public function show($customer, $job, Request $request, PreviousOrder $previousOrder, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, ServiceV2MinimalTransformer $service_transformer)
    {
        $customer = $request->customer;
        $job = $request->job->load(['resource.profile', 'carRentalJobDetail', 'category', 'review', 'jobServices', 'discounts', 'complains' => function ($q) use ($customer) {
            $q->select('id', 'job_id', 'status', 'complain', 'complain_preset_id')
                ->whereHas('accessor', function ($query) use ($customer) {
                    $query->where('accessors.model_name', get_class($customer));
                });
        }]);

        $job->partnerOrder->calculate(true);
        if (!$job->partnerOrder->order->deliveryAddress) {
            $job->partnerOrder->order->deliveryAddress = $job->partnerOrder->order->getTempAddress();
        }

        $delivery_discount = 0;
        $calculated_job = $job->calculate(true);
        if (isset($calculated_job->otherDiscountsByType[DiscountTypes::DELIVERY]))
            $delivery_discount = $calculated_job->otherDiscountsByType[DiscountTypes::DELIVERY];

        $logistic_paid = $job->logistic_paid;
        $logistic_charge = $job->logistic_charge;

        if ($logistic_paid > $logistic_charge) $logistic_paid = $logistic_charge;
        $logistic_due = ($logistic_charge - $logistic_paid);

        $job_collection = collect();
        $job_collection->put('id', $job->id);
        $job_collection->put('resource_name', $job->resource ? $job->resource->profile->name : null);
        $job_collection->put('resource_picture', $job->resource ? $job->resource->profile->pro_pic : null);
        $job_collection->put('resource_mobile', $job->resource ? $job->resource->profile->mobile : null);
        $job_collection->put('customer_identity', $customer->getIdentityAttribute());
        $job_collection->put('delivery_address', $job->partnerOrder->order->deliveryAddress->address);
        $job_collection->put('delivery_name', $job->partnerOrder->order->deliveryAddress->name);
        $job_collection->put('delivery_mobile', $job->partnerOrder->order->deliveryAddress->mobile);
        $job_collection->put('additional_information', $job->job_additional_info);
        $job_collection->put('schedule_date', $job->schedule_date);
        $job_collection->put('schedule_date_readable', (Carbon::parse($job->schedule_date))->format('jS F, Y'));
        $job_collection->put('complains', $this->formatComplains($job->complains));
        $job_collection->put('preferred_time', $job->readable_preferred_time);
        $job_collection->put('category_id', $job->category ? $job->category->id : null);
        $job_collection->put('category_name', $job->category ? $job->category->name : null);
        $job_collection->put('category_image', $job->category ? $job->category->thumb : null);
        $job_collection->put('master_category_id', $job->category && $job->category->parent? $job->category->parent->id : null);
        $job_collection->put('master_category_name', $job->category && $job->category->parent? $job->category->parent->name : null);
        $job_collection->put('min_order_amount', $job->category ? $job->category->min_order_amount : null);
        $job_collection->put('partner_id', $job->partnerOrder->partner ? $job->partnerOrder->partner->id : null);
        $job_collection->put('partner_name', $job->partnerOrder->partner ? $job->partnerOrder->partner->name : null);
        $job_collection->put('partner_image', $job->partnerOrder->partner ? $job->partnerOrder->partner->getContactResourceProPic() : null);
        $job_collection->put('partner_mobile', $job->partnerOrder->partner ? $job->partnerOrder->partner->getContactNumber() : null);
        $job_collection->put('partner_address', $job->partnerOrder->partner ? $job->partnerOrder->partner->address : null);
        $job_collection->put('status', $job->status);
        $job_collection->put('rating', $job->review ? $job->review->rating : null);
        $job_collection->put('review', $job->review ? $job->review->calculated_review : null);
        $job_collection->put('original_price', (double)$job->partnerOrder->jobPricesWithLogistic);
        $job_collection->put('discount', (double)$job->partnerOrder->totalDiscount);
        $job_collection->put('payment_method', $this->formatPaymentMethod($job->partnerOrder->payment_method));
        $job_collection->put('price', (double)$job->partnerOrder->grossAmountWithLogistic);
        $job_collection->put('isDue', $job->partnerOrder->isDueWithLogistic() ? 1 : 0);
        $job_collection->put('due', $job->partnerOrder->getCustomerPayable());
        $job_collection->put('isRentCar', $job->isRentCar());
        $job_collection->put('is_on_premise', $job->isOnPremise());
        $job_collection->put('customer_favorite', $job->customerFavorite ? $job->customerFavorite->id : null);
        $job_collection->put('order_code', $job->partnerOrder->order->code());
        $job_collection->put('pick_up_location', $job->carRentalJobDetail && $job->carRentalJobDetail->pickUpLocation ? $job->carRentalJobDetail->pickUpLocation->name : null);
        $job_collection->put('pick_up_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null);
        $job_collection->put('pick_up_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->pick_up_address_geo) : null);
        $job_collection->put('destination_location', $job->carRentalJobDetail && $job->carRentalJobDetail->destinationLocation ? $job->carRentalJobDetail->destinationLocation->name : null);
        $job_collection->put('destination_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null);
        $job_collection->put('destination_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->destination_address_geo) : null);
        $job_collection->put('drop_off_date', $job->carRentalJobDetail ? (Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y')) : null);
        $job_collection->put('drop_off_time', $job->carRentalJobDetail ? (Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A')) : null);
        $job_collection->put('estimated_distance', $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null);
        $job_collection->put('estimated_time', $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null);
        $job_collection->put('can_take_review', $this->canTakeReview($job));
        $job_collection->put('can_pay', $this->canPay($job));
        $job_collection->put('can_add_promo', $this->canAddPromo($job));
        $job_collection->put('can_reschedule', $job->canReschedule() && ($this->checkPreparationTime($job) || $job->isScheduleDue()) ? 1 : 0);
        $job_collection->put('can_cancel', $job->canCancel() ? 1 : 0);
        $job_collection->put('is_vat_applicable', $job->category ? $job->category['is_vat_applicable'] : null);
        $job_collection->put('max_order_amount', $job->category ? (double)$job->category['max_order_amount'] : null);
        $job_collection->put('is_same_service', 0);
        $job_collection->put('is_closed', $job->partnerOrder->closed_at != null ? 1 : 0);
        $job_collection->put('is_inspection_service', $job->jobServices[0] ? $job->jobServices[0]->service->is_inspection_service :  0);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        if (count($job->jobServices) == 0) {
            $services = collect();
            $variables = json_decode($job->service_variables);
            $location_service = $job->service->locationServices->first();
            $upsell_calculation->setService($job->service)
                ->setOption(json_decode($job->service_option, true))
                ->setQuantity($job->service_quantity);
            if ($location_service) {
                $upsell_calculation->setLocationService($location_service);
            }
            $upsell_price = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
            $services->push([
                'service_id' => $job->service->id,
                'name' => $job->service_name,
                'variables' => $variables,
                'quantity' => $job->service_quantity,
                'unit' => $job->service->unit,
                'option' => $job->service_option,
                'variable_type' => $job->service_variable_type,
                'thumb' => $job->service->app_thumb,
                'fixed_upsell_price' => $upsell_price
            ]);
        } else {
            $services = collect();
            $location_services = LocationService::where('location_id', $job->partnerOrder->order->location_id)
                ->whereIn('service_id', $job->jobServices->pluck('service_id')->toArray())->get();
            foreach ($job->jobServices as $jobService) {
                /** @var JobService $jobService */
                $variables = json_decode($jobService->variables);
                $location_service = $location_services->where('service_id', $jobService->service->id)->first();
                $option = json_decode($jobService->option, true);
                $upsell_calculation->setService($jobService->service)
                    ->setOption($option ? $option : [])
                    ->setQuantity($jobService->quantity);
                if ($location_service) {
                    $upsell_calculation->setLocationService($location_service);
                }
                $upsell_price = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
                $selected_service = [
                    "option" => json_decode($jobService->option, true),
                    "variable_type" => $jobService->variable_type
                ];
                if ($location_service) {
                    $service_transformer->setLocationService($location_service);
                }
                $resource = new Item($selected_service, $service_transformer);
                $price_data = $manager->createData($resource)->toArray();

                $service_data = [
                    'service_id' => $jobService->service->id,
                    'name' => $jobService->formatServiceName($job),
                    'variables' => $variables,
                    'unit' => $jobService->service->unit,
                    'quantity' => $jobService->quantity,
                    'option' => $jobService->option,
                    'variable_type' => $jobService->variable_type,
                    'thumb' => $jobService->service->app_thumb,
                    'upsell_price' => $upsell_price
                ];
                $service_data += $price_data;
                $services->push($service_data);
            }
            $job_collection->put('is_same_service', $previousOrder->setCategory($job->category)
                ->setJobServices($job->jobServices)
                ->setLocationServices($location_services)->canOrder());
        }
        $job_collection->put('services', $services);
        $resource = new Item($job->category, new ServiceV2DeliveryChargeTransformer($delivery_charge, $job_discount_handler, $job->partnerOrder->order->location));
        $delivery_charge_discount_data = $manager->createData($resource)->toArray();
        $job_collection->put('delivery_charge', $delivery_charge_discount_data['delivery_charge']);
        $job_collection->put('delivery_discount', $delivery_charge_discount_data['delivery_discount']);
        return api_response($request, $job_collection, 200, ['job' => $job_collection]);
    }

    private function checkPreparationTime(Job $job)
    {
        $preparation_time = $job->category->preparation_time_minutes;
        $now = Carbon::now();

        $job_schedule = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);;
        $current_time_with_preparation = $job_schedule->subMinutes(60)->subMinutes($preparation_time);

        return ($now <= $current_time_with_preparation);
    }

    /**
     * @param $status
     * @return bool
     */
    public function isStatusBeforeProcess($status)
    {
        return constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[$status] < constants('JOB_STATUS_SEQUENCE_FOR_ACTION')[JobStatuses::PROCESS];
    }

    private function formatComplains($complains)
    {
        foreach ($complains as &$complain) {
            $complain['code'] = $complain->code();
        }
        return $complains;
    }

    private function canAddPromo(Job $job)
    {
        $partner_order = $job->partnerOrder;
        return (double)$job->totalDiscount == 0 && !$partner_order->order->voucher_id && $partner_order->due != 0 && !$partner_order->cancelled_at && !$partner_order->closed_at ? 1 : 0;
    }

    public function getBills($customer, $job, Request $request, OrderRepository $logistics_orderRepo, FormatServices $formatServices, FromGeo $from_geo)
    {
        $job = $request->job->load(['partnerOrder.order', 'category', 'service', 'jobServices' => function ($q) {
            $q->with('service');
        }]);
        $job->calculate(true);
        if (count($job->jobServices) == 0) {
            $services = array();
            $service_list = array();
            array_push($services, [
                'name' => $job->service != null ? $job->service->name : null,
                'price' => (double)$job->servicePrice,
                'min_price' => 0,
                'is_min_price_applied' => 0
            ]);
            array_push($service_list, [
                'name' => $job->service != null ? $job->service->name : null,
                'service_group' => [],
                'unit' => $job->service->unit,
                'quantity' => $job->service_quantity,
                'price' => (double)$job->servicePrice
            ]);
        } else {
            $service_list = $formatServices->setJob($job)->formatServices();
            $services = array();
            foreach ($job->jobServices->sortByDesc('id') as $jobService) {
                $total = (double)$jobService->unit_price * (double)$jobService->quantity;
                $min_price = (double)$jobService->min_price;
                array_push($services, array(
                    'name' => $jobService->service != null ? $jobService->service->name : null,
                    'quantity' => $jobService->quantity,
                    'job_service_id' => $jobService->id,
                    'service_id' => $jobService->service->id,
                    'price' => $total,
                    'min_price' => $min_price,
                    'is_min_price_applied' => $min_price > $total ? 1 : 0
                ));
            }
        }
        $payment_method_names = [
            "cbl" => "City Bank",
            "Ssl" => "Other Debit/Credit",
            "Wallet" => "Sheba Credit",
            "Bonus" => "Sheba Credit",
            "Bondhu_balance" => "Bondhu Point"
        ];
        $partnerOrder = $job->partnerOrder;
        $methods_with_amounts = $partnerOrder->payments()->select('method','amount')->get()->toArray();
        foreach ($methods_with_amounts as &$method) {
            if(!array_key_exists($method['method'], $payment_method_names)) $method['name'] = $method['method'];
            else $method['name'] = $payment_method_names[$method['method']];
        }
        $partnerOrder->calculate(true);
        $original_delivery_charge = $job->deliveryPrice;
        $delivery_discount = $job->deliveryDiscount;
        $voucher = $partnerOrder->order->voucher ? [
            'code' => $partnerOrder->order->voucher->code,
            'amount' => $partnerOrder->order->voucher->amount
        ] : null;

        $from_geo = $from_geo->setThanas();

        $pickup_geo = $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->pick_up_address_geo, true) : null;
        $pickup_thana = $pickup_geo ? $from_geo->getThana($pickup_geo['lat'], $pickup_geo['lng']) : null;
        $destination_geo = $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->destination_address_geo, true) : null;
        $destination_thana = $destination_geo ? $from_geo->getThana($destination_geo['lat'], $destination_geo['lng']) : null;

        $bill = collect();
        $bill['total'] = (double)($partnerOrder->totalPrice + $partnerOrder->totalLogisticCharge);
        $bill['total_without_logistic'] = (double)($partnerOrder->totalPrice);
        $bill['original_price'] = (double)$partnerOrder->jobPrices;
        $bill['paid'] = (double)$partnerOrder->paidWithLogistic;
        $bill['due'] = (double)$partnerOrder->dueWithLogistic;
        $bill['grand_total'] = (double)$partnerOrder->grandTotal;
        $bill['vat'] = $job->vat;
        $bill['vat_percentage'] = config('sheba.category_vat_in_percentage');
        $bill['material_price'] = (double)$job->materialPrice;
        $bill['total_service_price'] = (double)$job->servicePrice;
        $bill['discount'] = (double)$job->discountWithoutDeliveryDiscount;
        $bill['payment_methods'] = $methods_with_amounts;
        $bill['services'] = $services;
        $bill['service_list'] = $service_list;
        $bill['category_name'] = $job->category->name;
        $bill['category_disclaimer'] = $job->category->disclaimer;
        $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
        $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
        $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
        $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
        $bill['payment_method'] = $this->formatPaymentMethod($partnerOrder->payment_method);
        $bill['status'] = $job->status;
        $bill['isRentCar'] = $job->isRentCar();
        $bill['is_on_premise'] = (int)$job->isOnPremise();
        $bill['delivery_charge'] = $original_delivery_charge;
        $bill['delivery_discount'] = $delivery_discount;
        $bill['invoice'] = $job->partnerOrder->invoice;
        $bill['version'] = $job->partnerOrder->getVersion();
        $bill['voucher'] = $voucher;
        $bill['is_vat_applicable'] = $job->category ? $job->category['is_vat_applicable'] : null;
        $bill['is_closed'] = $partnerOrder['closed_at'] ? 1 : 0;
        $bill['max_order_amount'] = $job->category ? (double)$job->category['max_order_amount'] : null;
        $bill['pick_up'] = $pickup_thana ? [
            'thana' => $pickup_thana->name
        ] : null;
        $bill['destination'] = $destination_thana ? [
            'thana' => $destination_thana->name
        ] : null;
        $bill['is_surcharge_applied'] = count($job->jobServices) > 0 && $job->jobServices[0] ? !!($job->jobServices[0]->surcharge_percentage) ? 1 : 0 : 0;
        $bill['surcharge_percentage'] = count($job->jobServices) > 0 && $job->jobServices[0] ? (double)$job->jobServices[0]->surcharge_percentage : 0;
        $bill['surcharge_amount'] = (double)$job->totalServiceSurcharge;

        return api_response($request, $bill, 200, ['bill' => $bill]);
    }

    private function formatPaymentMethod($payment_method)
    {
        if ($payment_method == 'Cash On Delivery' || $payment_method == 'cash-on-delivery') return 'cod';
        if ($payment_method == 'ssl' || $payment_method == 'port wallet') return 'online';
        return strtolower($payment_method);
    }

    public function getLogs($customer, $job, Request $request)
    {
        try {
            $all_logs = collect();
            $this->formatLogs((new JobLogs($request->job))->all(), $all_logs);
            $dates = $all_logs->sortByDesc(function ($item, $key) {
                return $item->get('timestamp');
            });
            return count($dates) > 0 ? api_response($request, $dates, 200, ['logs' => $dates->values()->all()]) : api_response($request, null, 404);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function formatLogs($job_logs, $all_logs)
    {
        foreach ($job_logs as $key => $job_log) {
            foreach ($job_log as $log) {
                $collect = collect($log);
                $collect->put('created_at', $log->created_at->toDateString());
                $collect->put('timestamp', $log->created_at->timestamp);
                $collect->put('type', $key);
                $collect->put('color_code', '#02adfc');
                if(substr_count($log->log, 'Commission Rate') === 0) $all_logs->push($collect);
            }
        }
    }

    private function getJobOfOrders($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            foreach ($order->partnerOrders as $partnerOrder) {
                $partnerOrder->calculateStatus();
                foreach ($partnerOrder->jobs as $job) {
                    $category = $job->category == null ? $job->service->category : $job->category;
                    $all_jobs->push(collect(array(
                        'job_id' => $job->id,
                        'category_name' => $category->name,
                        'category_thumb' => $category->thumb,
                        'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
                        'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
                        'status' => $job->status,
                        'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
                        'partner_name' => $partnerOrder->partner->name,
                        'rating' => $job->review != null ? $job->review->rating : null,
                        'order_code' => $order->code(),
                        'created_at' => $job->created_at->format('Y-m-d'),
                        'created_at_timestamp' => $job->created_at->timestamp,
                        //'can_take_review' => $this->canTakeReview($job),
                        'message' => (new JobLogs($job))->getOrderMessage()
                    )));
                }
            }
        }
        return $all_jobs;
    }

    protected function canTakeReview($job)
    {
        if (!$job) return false;
        $review = $job->review;

        if (!is_null($review) && $review->rating > 0) {
            return false;
        } else if ($job->partnerOrder->closed_at) {
            $closed_date = Carbon::parse($job->partnerOrder->closed_at);
            $now = Carbon::now();
            $difference = $closed_date->diffInDays($now);

            return $difference < constants('CUSTOMER_REVIEW_OPEN_DAY_LIMIT');
        } else {
            return false;
        }
    }

    protected function canPay($job)
    {
        $status = $job->status;

        if (in_array($status, ['Declined', 'Cancelled']) || $job->cancelRequests()->where('status', CancelRequestStatuses::PENDING)->first())
            return false;
        else {
            return $job->partnerOrder->getCustomerPayable() > 0;
        }
    }

    public function getInfo($customer, $job, Request $request)
    {
        $job = Job::find($job);
        if ($job != null) {
            if ($job->partner_order->order->customer_id == $customer) {
                $job = Job::with(['partner_order' => function ($query) {
                    $query->select('id', 'partner_id', 'order_id')->with(['partner' => function ($query) {
                        $query->select('id', 'name');
                    }])->with(['order' => function ($query) {
                        $query->select('id');
                    }]);
                }])->with(['resource' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name', 'mobile', 'pro_pic');
                    }]);
                }])->with(['usedMaterials' => function ($query) {
                    $query->select('id', 'job_id', 'material_name', 'material_price');
                }])->with(['service' => function ($query) {
                    $query->select('id', 'name', 'unit');
                }])->with(['review' => function ($query) {
                    $query->select('job_id', 'review_title', 'review', 'rating');
                }])->where('id', $job->id)
                    ->select('id', 'service_id', 'resource_id', DB::raw('DATE_FORMAT(schedule_date, "%M %d, %Y") as schedule_date'),
                        DB::raw('DATE_FORMAT(delivered_date, "%M %d, %Y at %h:%i %p") as delivered_date'), 'created_at', 'preferred_time',
                        'service_name', 'service_quantity', 'service_variable_type', 'service_variables', 'job_additional_info', 'service_option', 'discount',
                        'status', 'service_unit_price', 'partner_order_id')
                    ->first();
                array_add($job, 'status_show', $this->jobStatusesShow[array_search($job->status, $this->jobStatuses)]);

                $job_model = Job::find($job->id);
                $job_model->calculate();
                array_add($job, 'material_price', $job_model->materialPrice);
                array_add($job, 'total_cost', $job_model->grossPrice);
                array_add($job, 'job_code', $job_model->fullCode());
                array_add($job, 'time', $job->created_at->format('jS M, Y'));
                array_forget($job, 'created_at');
                array_add($job, 'service_price', $job_model->servicePrice);
                if ($job->resource != null) {
                    $profile = $job->resource->profile;
                    array_forget($job, 'resource');
                    $job['resource'] = $profile;
                } else {
                    $job['resource'] = null;
                }

                return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
            } else {
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
            }
        } else {
            return api_response($request, null, 404);
        }
    }

    public function getPreferredTimes()
    {
        return response()->json(['times' => config('constants.JOB_PREFERRED_TIMES'), 'valid_times' => $this->getSelectableTimes(), 'code' => 200]);
    }

    private function getSelectableTimes()
    {
        $today_slots = [];
        foreach (constants('JOB_PREFERRED_TIMES') as $time) {
            if ($time == "Anytime" || Carbon::now()->lte(Carbon::createFromTimestamp(strtotime(explode(' - ', $time)[1])))) {
                $today_slots[$time] = $time;
            }
        }
        return $today_slots;
    }

    public function cancelJobReasons()
    {
        return response()->json(['reasons' => config('constants.JOB_CANCEL_REASONS_FROM_CUSTOMER'), 'code' => 200]);
    }

    public function cancel($customer, $job, Request $request)
    {
        $this->validate($request, [
            'cancel_reason' => 'required|exists:job_cancel_reasons,key,is_published_for_customer,1',
            'cancel_reason_details' => 'sometimes|string'
        ]);

        $customer = $request->customer;
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $job . '/change-status',
            [
                'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                    'customer_id' => $customer->id,
                    'remember_token' => $customer->remember_token,
                    'status' => constants('JOB_STATUSES')['Cancelled'],
                    'cancel_reason' => $request->cancel_reason,
                    'cancel_reason_details' => $request->cancel_reason_details,
                    'created_by_type' => get_class($customer)
                ])
            ]);
        if ($response = json_decode($res->getBody())) return api_response($request, $response, $response->code);

        return api_response($request, null, 500);
    }

    public function saveFavorites($customer, $job, Request $request)
    {
        try {
            $job = $request->job;
            try {
                DB::transaction(function () use ($customer, $job) {
                    $favorite = new CustomerFavorite(['category_id' => $job->category_id, 'name' => $job->category->name, 'additional_info' => $job->additional_info]);
                    $customer->favorites()->save($favorite);
                    foreach ($job->jobServices as $jobService) {
                        $favorite->services()->attach($jobService->service_id, [
                            'name' => $jobService->service->name, 'variable_type' => $jobService->variable_type,
                            'variables' => $jobService->variable,
                            'option' => $jobService->option,
                            'quantity' => (double)$jobService->min_quantity
                        ]);
                    }
                });
                return api_response($request, 1, 200);
            } catch (QueryException $e) {
                return api_response($request, null, 500);
            }
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $customer
     * @param $job
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @param OrderAdapter $order_adapter
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearBills($customer, $job, Request $request, PaymentManager $payment_manager, OrderAdapter $order_adapter)
    {
        $this->validate($request, [
            'payment_method' => 'sometimes|required|in:online,wallet,bkash,cbl,partner_wallet,nagad',
            'emi_month' => 'numeric'
        ]);
        $payment_method = $request->has('payment_method') ? $request->payment_method : 'online';
        $order_adapter->setPartnerOrder($request->job->partnerOrder)->setPaymentMethod($payment_method)->setEmiMonth($request->emi_month);
        $payment = $payment_manager->setMethodName($payment_method)->setPayable($order_adapter->getPayable())->init();
        return api_response($request, $payment, 200, ['link' => $payment->redirect_url, 'payment' => $payment->getFormattedPayment()]);
    }

    public function getOrderLogs($customer, Request $request)
    {
        try {
            $job = $request->job;
            $logs = (new JobLogs($job))->getOrderStatusLogs();
            return api_response($request, $logs, 200, ['logs' => $logs]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getFaqs(Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'required'
            ]);
            $status = strtolower($request->status);
            $faqs = [];
            if (in_array($status, ['pending', 'not responded', 'declined'])) {
                $faqs = array(
                    array(
                        'question' => 'When my order will be confirmed?',
                        'answer' => 'When you placed an order, service provider will be notified. It will take 5-10 minutes for Service provider to accept the order. You can also call service provider to confirm.'
                    ),
                    array(
                        'question' => 'What if Service Provider declined my order?',
                        'answer' => 'If service provider declined to serve your order at that moment, Sheba.xyz will notify you and assign a new suitable service provider for you.'
                    ),
                    array(
                        'question' => 'How can I change service provider?',
                        'answer' => 'You can’t change service provider after confirming by service provider. In this case you can call 16516 or directly chat with us for help.'
                    ),
                    array(
                        'question' => 'Who will come to work on my order?',
                        'answer' => 'After confirming the order, Service provider will assign an expert for the order. Expert will come to work on your order on schedule time and date.'
                    ),
                    array(
                        'question' => 'What if I used the wrong payment method?',
                        'answer' => 'Unfortunately, you can’t change payment methods after placing the order. For more information, directly chat with us.'
                    ),
                    array(
                        'question' => 'What if my service provider or expert aren’t receiving my call?',
                        'answer' => 'If you failed to catch service provider or expert by several call, you can create an issue or chat with us. '
                    )
                );
            } elseif (in_array($status, ['accepted', 'schedule due', 'process', 'serve due'])) {
                $faqs = array(
                    array(
                        'question' => 'What if I want to reschedule the order?',
                        'answer' => 'You can reschedule your order by calling service provider. You can check your new schedule time and date from order details page.'
                    ),
                    array(
                        'question' => 'What if I pay advance to Service Provider?',
                        'answer' => 'If you pay in advance to service provider, bill section will be updated. You can check the bill section to know in details about the bill.'
                    ),
                    array(
                        'question' => 'What if I used the wrong payment method?',
                        'answer' => 'Unfortunately, you can’t change payment methods after placing the order. For more information, directly chat with us.'
                    ),
                    array(
                        'question' => 'Who will come to work on my order?',
                        'answer' => 'After confirming the order, Service provider will assign an expert for the order. Expert will come to work on your order on schedule time and date.'
                    ),
                    array(
                        'question' => 'What if my order is not started in schedule time?',
                        'answer' => 'You will get a notification 30 minutes before your selected schedule slots. If your order hasn’t started in time, you can call expert to know the issue or you can let Sheba.xyz know by creating an issue.'
                    ),
                    array(
                        'question' => 'What if my service provider or expert isn’t receiving my call?',
                        'answer' => 'If you failed to catch service provider or expert by several call, you can create an issue or chat with us.'
                    ),
                );
            } elseif (in_array($status, ['served'])) {
                $faqs = array(
                    array(
                        'question' => 'What if expert asks for additional payment?',
                        'answer' => 'Expert or Service Provider should not ask for extra payment. You don’t need to pay any additional payment. Tips are not also expected or required. If you wish to tip, the adjustment to the total bill will not be made. If expert asks for any additional payment which is not found in app, create an issue or directly chat with us'
                    ),
                    array(
                        'question' => 'What if I want to create an issue against my service provider and expert?',
                        'answer' => 'You can create an issue by clicking ‘Get Support’ option from the order details page. Sheba.xyz support team will receive the issue and solve it within 72 hours.'
                    ),
                    array(
                        'question' => 'What can I do if I am not satisfied with the service quality?',
                        'answer' => 'You can rate your experience so that service provider will take action against the expert or you can create an issue from ‘Get Support’ option'
                    )
                );
            } elseif (in_array($status, ['cancelled'])) {
                $faqs = array(
                    array(
                        'question' => 'What if my order is mistakenly cancelled?',
                        'answer' => 'In this case, you can directly chat with us informing about the issue. Our support management team will look after the issue.'
                    ),
                    array(
                        'question' => 'What if someone asks me for cancellation fee?',
                        'answer' => 'Currently we don’t have any cancellation fee. If any expert or service provider ask you for the cancellation fee, kindly message us from message section of the app.'
                    ),
                    array(
                        'question' => 'What if I paid online and my order is cancelled?',
                        'answer' => 'Our Support management team will look after this issue. They will investigate and refund at your account within 72 working hours.'
                    )
                );
            }
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function cancelReason(Request $request)
    {
        try {
            $job_cancel_reasons = JobCancelReason::ForCustomer()->select('id', 'name', 'key')->get();
            return api_response($request, $job_cancel_reasons, 200, ['cancel-reason' => $job_cancel_reasons]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getInvoice(Request $request)
    {
        $invoice = $this->generateInvoiceOfJob($request->job);
        return api_response($request, $invoice, 200, ['invoice' => $invoice]);
    }

    public function generateInvoiceOfJob(Job $job)
    {
        $invoice = null;
        if($job->isServed()) {
            return [
                'link' => $job->partnerOrder->invoice
            ];
        }
        return (new InvoiceHandler($job->partnerOrder))->save('quotation');
    }

    public function rescheduleJob($customer, $job, Request $request, Reschedule $reschedule_job, UserAgentInformation $user_agent_information)
    {
        $this->validate($request, ['schedule_date' => 'string', 'schedule_time_slot' => 'string']);

        $job = Job::find($job);
        if ($job == null) return api_response($request, null, 404);

        $user_agent_information->setRequest($request);

        $customer = Customer::find($customer);

        $reschedule_job
            ->setCustomer($customer)
            ->setJob($job)
            ->setUserAgentInformation($user_agent_information)
            ->setScheduleDate($request->schedule_date)
            ->setScheduleTimeSlot($request->schedule_time_slot);

        $response = $reschedule_job->reschedule();

        $res = ['message' => $response['msg']];
        if(!empty($response['job_id'])) $res['job_id'] = $response['job_id'];

        return api_response($request, $response, $response['code'], $res);
    }
}
