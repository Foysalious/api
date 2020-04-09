<?php namespace Sheba\Logistics;

use App\Models\Job;
use App\Models\Partner;
use Exception;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\DTO\VendorOrder;
use Sheba\Logistics\Exceptions\LogisticServerError;
use Sheba\Logistics\Literals\OrderKeys;
use Sheba\Logistics\LogisticsNatures\NatureFactory;
use Sheba\Logistics\Repository\OrderRepository;

class OrderManager
{
    /** @var OrderRepository */
    private $repo;

    /** @var Job */
    private $job;

    public function __construct(OrderRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param Job $job
     * @return $this
     * @throws \Exception
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
        $base_url = url('api/job/' . $this->job->id);
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
            ->setParcelType($this->job->category->logistic_parcel_type)
            ->setSuccessUrl($base_url . '/logistic-completed')
            ->setPickedUrl($base_url . '/logistic-picked')
            ->setFailureUrl($base_url . '/logistic-completed')
            ->setPayUrl($base_url . '/logistic-paid')
            ->setCollectionUrl($base_url . '/collect-by-logistic')
            ->setRiderNotFoundUrl($base_url . '/rider-not-found');

        $logistic_order = $this->repo->store($order->toArray());

        return $order->setId($logistic_order['id']);
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
        $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id']);

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
    public function changeDrop(Partner $new_partner)
    {
        $logistic_nature = NatureFactory::getLogisticNature($this->job, OrderKeys::FIRST);
        $order = $this->get($this->job->first_logistic_order_id);
        $new_pick_point = $logistic_nature->createPartnerPoint($new_partner);
        $data = $order->setPickUp($new_pick_point)->getPickUpArray();
        $this->repo->update($order, $data);
    }

    public function updateVendorCollectable(Order $order, $amount)
    {
        $order->setCollectableAmount($amount);
        $this->repo->update($order, $order->getCollectableUpdateArray());
    }

}