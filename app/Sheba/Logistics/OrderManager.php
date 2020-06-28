<?php namespace Sheba\Logistics;

use App\Models\Job;
use App\Models\Partner;
use App\Models\Vendor;
use Exception;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\DTO\VendorOrder;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Literals\OrderKeys;
use Sheba\Logistics\LogisticsNatures\NatureFactory;
use Sheba\Logistics\Repository\OrderRepository;
use Sheba\PartnerOrder\PartnerOrderCollectionTransferToLogistic;
use Sheba\Repositories\JobRepository;
use Sheba\Vendor\VendorTransactionHandler;

class OrderManager
{
    /** @var OrderRepository */
    private $repo;
    /** @var VendorTransactionHandler */
    private $vendorTransactionHandler;

    /** @var Job */
    private $job;

    public function __construct(OrderRepository $repo, VendorTransactionHandler $vendor_transaction_handler)
    {
        $this->repo = $repo;
        $this->vendorTransactionHandler = $vendor_transaction_handler;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param $key
     * @return Order
     * @throws LogisticServerError
     */
    public function create($key)
    {
        $detail_url = config('sheba.api_url') . '/v1/vendors/orders/' . $this->job->partnerOrder->order_id;
        $logistic_nature = NatureFactory::getLogisticNature($this->job, $key);

        $vendor_order = (new VendorOrder())->setDetailUrl($detail_url)->setBillUrl($detail_url . '/bills')
            ->setCode($this->job->partner_order->order->code())->setId($this->job->partner_order->order->id);

        $order = (new Order())->setPickUp($logistic_nature->getPickUp())
            ->setDropOff($logistic_nature->getDropOff())
            ->setSchedule($logistic_nature->getPickupTime())
            ->setIsInstant($logistic_nature->isInstant())
            ->setCollectableAmount($logistic_nature->getCollectableAmount())
            ->setDiscountByArray($logistic_nature->getDiscount())
            ->setPaidAmount($logistic_nature->getPaidAmount())
            ->setCustomerProfileId($this->job->partnerOrder->order->customer->profile->id)
            ->setVendorOrder($vendor_order)
            ->setParcelType($this->job->category->logistic_parcel_type);

        $order = $this->changeHooks($order);

        $logistic_order = $this->repo->store($order->toArray());

        return $order->setId($logistic_order['id']);
    }

    /**
     * @param Job $job
     * @param Order $order
     * @return Order|null
     */
    public function changeHooks(Order $order, Job $job = null)
    {
        $base_url = url('api/job/' . ($job ? $job->id : $this->job->id));

        $order->setSuccessUrl($base_url . '/logistic-completed')
            ->setPickedUrl($base_url . '/logistic-picked')
            ->setFailureUrl($base_url . '/logistic-completed')
            ->setPayUrl($base_url . '/logistic-paid')
            ->setCollectionUrl($base_url . '/collect-by-logistic')
            ->setRiderNotFoundUrl($base_url . '/rider-not-found');

        return $order;
    }

    /**
     * @param $order_id
     * @return Order
     * @throws Exceptions\LogisticServerError
     */
    public function get($order_id)
    {
        $data = $this->repo->find($order_id);
        $order = new Order();
        $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id'])->setVendorCollectableAmount($data['vendor_collectable_amount']);
        return $order;
    }

    /**
     * @param $order_id
     * @return Order
     * @throws Exceptions\LogisticServerError
     */
    public function getMinimal($order_id)
    {
        $data = $this->repo->findMinimal($order_id);
        $order = new Order();
        $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id']);

        return $order;
    }

    /**
     * @param $order_ids
     * @return mixed
     */
    public function getMinimals($order_ids)
    {
        return $this->repo->findMinimals($order_ids);
    }

    /**
     * @param $date
     * @param $time
     * @throws Exceptions\LogisticServerError
     * @throws Exception
     */
    public function reschedule($date, $time)
    {
        $logistic_nature = NatureFactory::getLogisticNature($this->job, OrderKeys::FIRST);
        $pick_up_time = $logistic_nature->getPickupTimeFromDateTime($date, $time);
        $order = $this->get($this->job->first_logistic_order_id);
        $this->repo->reschedule($order, $pick_up_time->toDateString(), $pick_up_time->toTimeString());
    }

    /**
     * @param Partner $new_partner
     * @throws LogisticServerError
     */
    public function updatePoint(Partner $new_partner)
    {
        $order = $this->get($this->job->first_logistic_order_id);
        $order = $this->changePoint($new_partner, $order);
        $this->update($order);
    }

    /**
     * @param Partner $new_partner
     * @param Order|null $order
     * @return Order
     * @throws LogisticServerError
     */
    public function changePoint(Partner $new_partner, Order $order = null)
    {
        $logistic_nature = NatureFactory::getLogisticNature($this->job, OrderKeys::FIRST);
        $order = $order ?: $this->get($this->job->first_logistic_order_id);
        $new_point = $logistic_nature->createPartnerPoint($new_partner);
        if ($this->job->category->needsOneWayLogistic()) {
            $order->setPickUp($new_point);
        } else {
            $order->setDropOff($new_point);
        }
        return $order;
    }

    /**
     * @param Order $order
     * @throws LogisticServerError
     */
    public function update(Order $order)
    {
        $this->repo->update($order, $order->getTouchedArray());
    }

    /**
     * @param Order $order
     * @param $amount
     * @throws Exception
     */
    public function pay(Order $order, $amount)
    {
        $this->payShebaOrder($order, $amount);
        $this->repo->pay($order, $amount);
    }

    /**
     * @param Order $order
     * @param $amount
     * @throws Exception
     */
    public function payShebaOrder(Order $order, $amount)
    {
        $partner_order = $this->job->partnerOrder;

        /** @var JobRepository $job_repo */
        $job_repo = app(JobRepository::class);
        $job_repo->update($this->job, [
            'logistic_paid' => $this->job->logistic_paid + $amount
        ]);

        $transfer = new PartnerOrderCollectionTransferToLogistic($partner_order, $amount);
        $transfer->process();

        $log = "Paid $amount for sheba order: " . $partner_order->code() . ", logistic order: " . $order->id;
        $this->vendorTransactionHandler->setVendor($this->getVendor())->credit($amount, $partner_order, $log);
    }

    /**
     * @param Order $order
     * @throws LogisticServerError
     */
    public function cancel(Order $order)
    {
        $res = $this->repo->cancel($order);
        $refunded = $res['refunded'];

        if ($refunded) {
            $partner_order = $this->job->partnerOrder;
            $log = "Refunded $refunded for sheba order: " . $partner_order->code() . ", logistic order: " . $order->id;
            $this->vendorTransactionHandler->setVendor($this->getVendor())->debit($refunded, $partner_order, $log);
        }
    }

    /**
     * @param Order $order
     * @param $amount
     * @throws LogisticServerError
     */
    public function updateVendorCollectable(Order $order, $amount)
    {
        $order->setCollectableAmount($amount);
        $this->repo->update($order, $order->getCollectableUpdateArray());
    }

    /** @return Vendor
     */
    private function getVendor()
    {
        return Vendor::find(config('sheba.logistic_vendor_id'));
    }
}
