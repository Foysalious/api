<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;
use Sheba\Logistics\Repository\ParcelRepository;

class OrderAdapter implements PayableAdapter
{
    private $partnerOrder;
    private $isAdvancedPayment;
    private $userId;
    private $userType;
    private $job;

    public function __construct(PartnerOrder $partner_order, $is_advanced_payment = false)
    {
        $this->partnerOrder = $partner_order;
        $this->partnerOrder->calculate(true);
        $this->isAdvancedPayment = $is_advanced_payment;
        $this->setUser();
    }

    public function getPayable(): Payable
    {
        $this->job = $this->partnerOrder->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first();

        $payable = new Payable();
        $payable->type = 'partner_order';
        $payable->type_id = $this->partnerOrder->id;
        $payable->user_id = $this->userId;
        $payable->user_type = $this->userType;
        $payable->amount = (double)$this->partnerOrder->due + $this->getShebaLogisticsPrice();
        $payable->completion_type = $this->isAdvancedPayment ? 'advanced_order' : "order";
        $payable->success_url = config('sheba.front_url') . '/orders/' . $this->job->id;
        $payable->created_at = Carbon::now();
        $payable->save();

        return $payable;
    }

    private function getShebaLogisticsPrice()
    {
        if ($this->job->needsLogistic()) {
            $parcel_repo = app(ParcelRepository::class);
            $parcel_details = $parcel_repo->findBySlug($this->category->logistic_parcel_type);

            return isset($parcel_details['price']) ? $parcel_details['price'] : 0;
        }

        return 0.00;
    }

    private function setUser()
    {
        $order = $this->partnerOrder->order;

        if ($order->partner_id) {
            $this->userId = $order->partner_id;
            $this->userType = "App\\Models\\Partner";
        } else {
            $this->userId = $order->customer_id;
            $this->userType = "App\\Models\\Customer";
        }
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }
}