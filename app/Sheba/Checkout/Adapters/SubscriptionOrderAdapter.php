<?php namespace Sheba\Checkout\Adapters;

use App\Models\Job;
use App\Models\JobService;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Models\Payable;
use App\Models\PaymentDetail;
use App\Models\Resource;
use App\Models\Service;
use App\Models\ServiceSubscriptionDiscount;
use App\Models\SubscriptionOrder;
use Sheba\Checkout\ShebaOrderInterface;
use Sheba\Checkout\SubscriptionOrderInterface;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use DB;

class SubscriptionOrderAdapter implements ShebaOrderInterface
{
    use ModificationFields;
    private $partnerServiceDetails;
    private $deliveryCharge;
    private $totalSchedules;
    /** @var SubscriptionOrder $subscriptionOrder */
    private $subscriptionOrder;
    /** @var PaymentDetail[] $paymentDetails */
    private $paymentDetails;
    /** @var PaymentDetail $bonusPaymentDetail */
    private $bonusPaymentDetail;
    /** @var PaymentDetail $otherPaymentDetail */
    private $otherPaymentDetail;

    public function __construct(SubscriptionOrderInterface $subscriptionOrder)
    {
        $this->subscriptionOrder = $subscriptionOrder;
    }

    public function setPaymentMethod()
    {

    }

    public function partnerOrders()
    {

    }

    public function jobs()
    {
        // TODO: Implement jobs() method.
    }

    public function convertToOrder()
    {
        if ($this->subscriptionOrder->orders->count() == 0) {
            $this->createOrders();
            return $this->subscriptionOrder;
        } else {
            return false;
        }
    }

    public function createOrders()
    {
        $this->setModifier($this->subscriptionOrder->customer);
        $this->setCalculatedProperties();
        DB::transaction(function () {
            $jobs = collect();
            foreach ($this->totalSchedules as $schedule) {
                $order = $this->createOrder();
                $partner_order = $this->createPartnerOrder($order);
                $job = $this->createJob($partner_order, $schedule);
                $jobs->push($job);
                $this->createJobServices($job);
            }
            $this->subscriptionOrder->status = 'converted';
            $this->subscriptionOrder->update();
            $this->bookResources($jobs);
        });
    }

    private function setCalculatedProperties()
    {
        $this->setPaymentDetails();
        $this->setTotalSchedules();
        $this->setPartnerServiceDetails();
        $this->setDeliveryCharge();
    }


    private function setPartnerServiceDetails()
    {
        $this->partnerServiceDetails = json_decode($this->subscriptionOrder->service_details);
    }

    private function setDeliveryCharge()
    {
        $this->deliveryCharge = $this->partnerServiceDetails->delivery_charge / count($this->totalSchedules);
    }

    private function setTotalSchedules()
    {
        $this->totalSchedules = $this->subscriptionOrder->schedules();
    }

    private function setPaymentDetails()
    {
        $this->paymentDetails = Payable::where('type_id', $this->subscriptionOrder->id)->where('type', 'subscription_order')->first()->payment->paymentDetails;
        $this->setBonus();
        $this->setOtherPaymentDetail();
    }

    private function setBonus()
    {
        $this->bonusPaymentDetail = $this->paymentDetails->where('method', 'bonus')->first();
    }

    private function setOtherPaymentDetail()
    {
        $this->otherPaymentDetail = $this->paymentDetails->reject(function ($payment_detail) {
            return $payment_detail->method == 'bonus';
        })->first();
    }

    private function createOrder(): Order
    {
        $order = new Order();
        $order->delivery_mobile = $this->subscriptionOrder->delivery_mobile;
        $order->delivery_name = $this->subscriptionOrder->delivery_name;
        $order->sales_channel = $this->subscriptionOrder->sales_channel;
        $order->location_id = $this->subscriptionOrder->location_id;
        $order->customer_id = $this->subscriptionOrder->customer_id;
        $order->delivery_address_id = $this->subscriptionOrder->delivery_address_id;
        $order->subscription_order_id = $this->subscriptionOrder->id;
        $order->fill((new RequestIdentification())->get());
        $this->withCreateModificationField($order);
        $order->save();
        return $order;
    }

    public function createPartnerOrder(Order $order): PartnerOrder
    {
        $partner_order = new PartnerOrder();
        $partner_order->order_id = $order->id;
        $partner_order->partner_id = $this->subscriptionOrder->partner_id;
        $partner_order->payment_method = strtolower($this->paymentDetails->last()->readable_method);
        $partner_order->sheba_collection = (int)$this->partnerServiceDetails->discounted_price > 0 ? $this->partnerServiceDetails->discounted_price / count($this->totalSchedules) : 0;
        $this->withCreateModificationField($partner_order);
        $partner_order->save();
        $this->createPartnerOrderPayment($partner_order);
        return $partner_order;
    }

    public function createJob(PartnerOrder $partnerOrder, $schedule): Job
    {
        $job = new Job();
        $job->category_id = $this->subscriptionOrder->category_id;
        $job->partner_order_id = $partnerOrder->id;
        $job->schedule_date = $schedule->date;
        $job->preferred_time = $schedule->time;
        $job->preferred_time_start = explode('-', $schedule->time)[0];
        $job->preferred_time_end = explode('-', $schedule->time)[1];
        $job->job_additional_info = $this->subscriptionOrder->additional_info;
        $job->category_answers = $this->subscriptionOrder->additional_info;
        $job->commission_rate = $this->subscriptionOrder->category->commission($this->subscriptionOrder->partner_id);
        $job->material_commission_rate = config('sheba.material_commission_rate');
        $job->status = constants('JOB_STATUSES')['Pending'];
        $job->delivery_charge = $this->deliveryCharge;
        $this->withCreateModificationField($job);
        $job->save();
        return $job;
    }

    public function createJobServices(Job $job)
    {
        $job_services = collect();
        foreach ($this->partnerServiceDetails->breakdown as $service) {
            $serviceModel = Service::find((int)$service->id);
            /** @var ServiceSubscriptionDiscount $discount */
            $discount = $serviceModel->subscription->discounts()->where('subscription_type', $this->subscriptionOrder->billing_cycle)->valid()->first();
            $service_data = array(
                'service_id' => $service->id,
                'quantity' => $service->quantity,
                'unit_price' => $service->unit_price,
                'min_price' => $service->min_price,
                'sheba_contribution' => $service->sheba_contribution,
                'partner_contribution' => $service->partner_contribution,
                'discount' => $service->discount,
                'discount_percentage' => $discount && $discount->isPercentage() ? $discount->discount_amount : 0,
                'name' => $service->name,
                'variable_type' => $serviceModel->variable_type,
            );
            $service_data = $this->withCreateModificationField($service_data);
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($serviceModel, $service->option);
            $job_services->push(new JobService($service_data));
        }
        $job->jobServices()->saveMany($job_services);
    }

    private function getVariableOptionOfService(Service $service, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'title' => isset($service_option->title) ? $service_option->title : null,
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $options = implode(',', $option);
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
        }
        return array($option, $variables);
    }

    private function createPartnerOrderPayment(PartnerOrder $partner_order)
    {
        $total_amount = $this->subscriptionOrder->sheba_collection;
        $collection_in_each_order = $total_amount ? $total_amount / count($this->totalSchedules) : 0;
        $remaining_collection = $collection_in_each_order;

        if ($this->bonusPaymentDetail && $this->bonusPaymentDetail->amount > 0) {
            $partner_order_payment = $this->getPartnerOrderPayment($partner_order);
            if ($this->bonusPaymentDetail->amount >= $collection_in_each_order) {
                $remaining_collection -= $collection_in_each_order;
                $this->bonusPaymentDetail->amount -= $collection_in_each_order;
                $partner_order_payment->amount = $collection_in_each_order;
            } else {
                $remaining_collection = $collection_in_each_order - $this->bonusPaymentDetail->amount;
                $partner_order_payment->amount = $this->bonusPaymentDetail->amount;
                $this->bonusPaymentDetail->amount = 0;
            }
            $partner_order_payment->transaction_detail = json_encode($this->bonusPaymentDetail->formatPaymentDetail());
            $partner_order_payment->method = ucfirst($this->bonusPaymentDetail->method);
            $partner_order_payment->save();
        }
        if ($remaining_collection > 0 && $this->otherPaymentDetail) {
            $partner_order_payment = $this->getPartnerOrderPayment($partner_order);
            $partner_order_payment->amount = $remaining_collection;
            $partner_order_payment->transaction_detail = json_encode($this->otherPaymentDetail->formatPaymentDetail());
            $partner_order_payment->method = ucfirst($this->otherPaymentDetail->method);
            $partner_order_payment->save();
        }

    }

    /**
     * @param PartnerOrder $partner_order
     * @return PartnerOrderPayment
     */
    private function getPartnerOrderPayment(PartnerOrder $partner_order)
    {
        $partner_order_payment = new PartnerOrderPayment();
        $partner_order_payment->partner_order_id = $partner_order->id;
        $partner_order_payment->transaction_type = 'Debit';
        $partner_order_payment->log = 'advanced payment';
        $partner_order_payment->collected_by = 'Sheba';
        $this->withCreateModificationField($partner_order_payment);
        $partner_order_payment->fill((new RequestIdentification())->get());
        return $partner_order_payment;
    }

    private function bookResources($jobs)
    {
        $resources = $this->getAvailableResources();
        foreach ($jobs as $job) {
            foreach ($resources as $resource) {
                $resource_handler = scheduler($resource);
                if ($resource_handler->isAvailable($job->schedule_date, $job->preferred_time_start)) {
                    $resource_handler->book($job);
                    $job->resource_id = $resource->id;
                    $job->update();
                    break;
                }
            }
        }
    }

    /**
     * @return Resource[]
     */
    private function getAvailableResources()
    {
        $dates = [];
        $schedules = $this->subscriptionOrder->schedules();
        $time = explode('-', $schedules[0]->time)[0];
        foreach ($schedules as $schedule) {
            array_push($dates, $schedule->date);
        }
        $resources = (scheduler($this->subscriptionOrder->partner))
            ->isAvailable($dates, $time, $this->subscriptionOrder->category)->get('available_resources');
        return Resource::whereIn('id', $resources)->get();
    }
}