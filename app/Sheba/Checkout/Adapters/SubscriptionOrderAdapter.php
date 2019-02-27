<?php namespace Sheba\Checkout\Adapters;

use App\Models\Job;
use App\Models\JobService;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Models\Service;
use App\Models\ServiceSubscriptionDiscount;
use Sheba\Checkout\ShebaOrderInterface;
use Sheba\Checkout\SubscriptionOrderInterface;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use DB;

class SubscriptionOrderAdapter implements ShebaOrderInterface
{
    use ModificationFields;
    private $subscriptionOrder;
    private $partnerServiceDetails;
    private $deliveryCharge;
    private $totalSchedules;

    public function __construct(SubscriptionOrderInterface $subscriptionOrder)
    {
        $this->subscriptionOrder = $subscriptionOrder;
    }

    public function partnerOrders()
    {
        if ($this->subscriptionOrder->orders->count() == 0) $this->createOrders();
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
            foreach ($this->totalSchedules as $schedule) {
                $order = $this->createOrder();
                $partner_order = $this->createPartnerOrder($order);
                $job = $this->createJob($partner_order, $schedule);
                $this->createJobServices($job);
            }
            $this->subscriptionOrder->status = 'converted';
            $this->subscriptionOrder->update();
        });
    }

    private function setCalculatedProperties()
    {
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
        $partner_order->payment_method = $this->subscriptionOrder->payment_method;
        $partner_order->sheba_collection = (int)$this->partnerServiceDetails->discounted_price > 0 ? $this->partnerServiceDetails->discounted_price / count($this->totalSchedules) : 0;
        $this->withCreateModificationField($partner_order);
        $partner_order->save();
//        $partner_order_payment = new PartnerOrderPayment();
//        $partner_order_payment->partner_order_id = $partner_order->id;
//        $partner_order_payment->transaction_type = 'Debit';
//        $partner_order_payment->amount = $partner_order->sheba_collection;
//        $partner_order_payment->log = 'advanced payment';
//        $partner_order_payment->collected_by = 'Sheba';
//        $partner_order_payment->transaction_detail = json_encode($paymentDetail->formatPaymentDetail());
//        $partner_order_payment->method = ucfirst($paymentDetail->method);
//        $this->withCreateModificationField($partner_order_payment);
//        $partner_order_payment->fill((new RequestIdentification())->get());
//        $partner_order_payment->save();
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
                'discount_id' => $discount->id,
                'discount' => $service->discount,
                'discount_percentage' => $discount->isPercentage() ? $discount->discount_amount : 0,
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

}