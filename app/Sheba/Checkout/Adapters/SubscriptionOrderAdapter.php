<?php namespace Sheba\Checkout\Adapters;

use App\Models\Job;
use App\Models\Payment;
use Sheba\Checkout\CommissionCalculator;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;
use Sheba\Dal\JobService\JobService;
use App\Models\Order;
use App\Models\PartnerOrder;
use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
use App\Models\Payable;
use App\Models\PaymentDetail;
use App\Models\Resource;
use Sheba\Dal\ServiceSubscriptionDiscount\ServiceSubscriptionDiscount;
use App\Models\SubscriptionOrder;
use Sheba\Checkout\SubscriptionOrderInterface;
use Sheba\Dal\SubscriptionOrder\Statuses as SubscriptionOrderStatuses;
use Sheba\Jobs\JobStatuses;
use Sheba\Jobs\PreferredTime;
use Sheba\ModificationFields;
use Sheba\Payment\Statuses as PaymentStatuses;
use Sheba\RequestIdentification;
use DB;

class SubscriptionOrderAdapter
{
    use ModificationFields;

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
    /** @var SubscriptionServicePricingAndBreakdown */
    private $servicePricingBreakdown;

    public function __construct(SubscriptionOrderInterface $subscription_order)
    {
        $this->subscriptionOrder = $subscription_order;
    }

    /**
     * @return SubscriptionOrder|bool|SubscriptionOrderInterface
     */
    public function convertToOrder()
    {
        if ($this->subscriptionOrder->orders->count() == 0) {
            $this->createOrders();
            return $this->subscriptionOrder;
        } else {
            $this->updateOrders();
            return false;
        }
    }

    private function createOrders()
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
            $this->subscriptionOrder->status = SubscriptionOrderStatuses::CONVERTED;
            $this->subscriptionOrder->update();
            $this->bookResources($jobs);
        });
    }

    private function setCalculatedProperties()
    {
        $this->setPaymentDetails();
        $this->setTotalSchedules();
        $this->setServicePricingBreakdown();
        $this->setDeliveryCharge();
    }

    private function setServicePricingBreakdown()
    {
        $this->servicePricingBreakdown = $this->subscriptionOrder->getServicesPriceBreakdown();
    }

    private function setDeliveryCharge()
    {
        $this->deliveryCharge = $this->servicePricingBreakdown->getDeliveryCharge() / count($this->totalSchedules);
    }

    private function setTotalSchedules()
    {
        $this->totalSchedules = $this->subscriptionOrder->schedules();
    }

    /**
     * @TODO for partial payment needs to change that
     */
    private function setPaymentDetails()
    {
        $payable = Payable::whereHas('payments', function ($q) {
            $q->where('status', PaymentStatuses::COMPLETED);
        })->where('type_id', $this->subscriptionOrder->id)->where('type', 'subscription_order')->first();
        if (!$payable) return;
        $this->paymentDetails = $payable->payments()->where('status', PaymentStatuses::COMPLETED)->first()->paymentDetails;
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
        $order->customer_id = $this->subscriptionOrder->customer->id;
        $order->delivery_address_id = $this->subscriptionOrder->delivery_address_id;
        $order->subscription_order_id = $this->subscriptionOrder->id;
        $order->fill((new RequestIdentification())->get());
        $this->withCreateModificationField($order);
        $order->save();
        return $order;
    }

    private function createPartnerOrder(Order $order): PartnerOrder
    {
        $partner_order = new PartnerOrder();
        $partner_order->order_id = $order->id;
        $partner_order->partner_id = $this->subscriptionOrder->partner_id;
        $partner_order->payment_method = $this->paymentDetails ? strtolower($this->paymentDetails->last()->readable_method) : null;
        $price = $this->servicePricingBreakdown->getDiscountedPrice();
        $partner_order->sheba_collection = $this->paymentDetails && $price > 0 ? $price / count($this->totalSchedules) : 0;
        $this->withCreateModificationField($partner_order);
        $partner_order->save();
        $this->createPartnerOrderPayment($partner_order);
        return $partner_order;
    }

    private function createJob(PartnerOrder $partnerOrder, $schedule): Job
    {
        $job = new Job();
        $job->category_id = $this->subscriptionOrder->category_id;
        $job->partner_order_id = $partnerOrder->id;
        $job->schedule_date = $schedule->date;
        $preferred_time = new PreferredTime($schedule->time);
        $job->preferred_time = $preferred_time->toString();
        $job->preferred_time_start = $preferred_time->getStartString();
        $job->preferred_time_end = $preferred_time->getEndString();
        $job->job_additional_info = $this->subscriptionOrder->additional_info;
        $job->category_answers = $this->subscriptionOrder->additional_info;
        if ($this->subscriptionOrder->partner) {
            $commissions = $this->getCommission();
            $job->commission_rate = $commissions->getServiceCommission();
            $job->material_commission_rate = $commissions->getMaterialCommission();
        }
        $job->status = JobStatuses::PENDING;
        $job->delivery_charge = $this->deliveryCharge;
        $this->withCreateModificationField($job);
        $job->save();

        return $job;
    }

    private function createJobServices(Job $job)
    {
        $job_services = collect();
        $services_with_price = $this->servicePricingBreakdown->getServices();
        foreach ($services_with_price as $service_with_price) {
            $service = $service_with_price->getService();
            /** @var ServiceSubscriptionDiscount $discount */
            $discount = $service->subscription->discounts()->where('subscription_type', $this->subscriptionOrder->billing_cycle)->valid()->first();
            $service_data = [
                'service_id' => $service_with_price->getId(),
                'quantity' => $service_with_price->getQuantity(),
                'unit_price' => $service_with_price->getUnitPrice(),
                'min_price' => $service_with_price->getMinPrice(),
                'sheba_contribution' => $service_with_price->getShebaContribution(),
                'partner_contribution' => $service_with_price->getPartnerContribution(),
                'discount' => $service_with_price->getDiscount(),
                'discount_percentage' => $discount && $discount->isPercentage() ? $discount->discount_amount : 0,
                'name' => $service_with_price->getName(),
                'variable_type' => $service->variable_type,
            ];
            $service_data = $this->withCreateModificationField($service_data);
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($service_with_price->getOption());
            $job_services->push(new JobService($service_data));
        }
        $job->jobServices()->saveMany($job_services);
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

    /**
     * @return CommissionCalculator
     */
    private function getCommission()
    {
        return (new CommissionCalculator())->setCategory($this->subscriptionOrder->category)->setPartner($this->subscriptionOrder->partner);
    }

    private function updateOrders()
    {
        PartnerOrder::whereHas('order', function ($q) {
            $q->whereHas('subscription', function ($q) {
                $q->where('subscription_orders.id', $this->subscriptionOrder->id);
            });
        })->update(['partner_id' => $this->subscriptionOrder->partner_id]);
        $commissions = $this->getCommission();
        Job::whereHas('partnerOrder', function ($q) {
            $q->whereHas('order', function ($q) {
                $q->whereHas('subscription', function ($q) {
                    $q->where('subscription_orders.id', $this->subscriptionOrder->id);
                });
            });
        })->update(['commission_rate' => $commissions->getServiceCommission(), 'material_commission_rate' => $commissions->getMaterialCommission()]);
    }
}
