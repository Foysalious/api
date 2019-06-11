<?php namespace Sheba\Logistics;

use App\Models\Category;
use App\Models\Job;
use Sheba\Logistics\Repository\OrderRepository;
use Sheba\Logistics\LogisticsNatures\NatureFactory;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\DTO\VendorOrder;

class OrderCreator
{
    /** @var OrderRepository */
    private $repo;
    /** @var Job */
    private $job;
    /** @var Category $category */
    private $category;
    private $key;

    public function __construct(OrderRepository $repo)
    {
        $this->repo = $repo;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function setOrderKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return Order
     * @throws \Exception
     */
    public function create()
    {
        $base_url = url('api/job/' . $this->job->id);
        $detail_url = config('sheba.api_url') . '/v1/vendors/orders/' . $this->job->partnerOrder->order_id;
        $logistic_nature = NatureFactory::getLogisticNature($this->job, $this->key);

        $order = (new Order())->setPickUp($logistic_nature->getPickUp())
            ->setDropOff($logistic_nature->getDropOff())
            ->setSchedule($logistic_nature->getPickupTime())
            ->setIsInstant($logistic_nature->isInstant())
            ->setCollectableAmount($logistic_nature->getCollectableAmount())
            ->setDiscountByArray($logistic_nature->getDiscount())
            ->setPaidAmount($logistic_nature->getPaidAmount())
            ->setCustomerProfileId($this->job->partnerOrder->order->customer->profile->id)
            ->setVendorOrder((new VendorOrder())->setDetailUrl($detail_url)->setBillUrl($detail_url . '/bills')->setCode($this->job->partner_order->order->code())->setId($this->job->partner_order->order->id))
            ->setParcelType($this->category->logistic_parcel_type)
            ->setSuccessUrl($base_url . '/logistic-completed')
            ->setPickedUrl($base_url . '/logistic-picked')
            ->setFailureUrl($base_url . '/logistic-completed')
            ->setCollectionUrl($base_url . '/collect-by-logistic');

        $logistic_order = $this->repo->store($order->toArray());

        return $order->setId($logistic_order['id']);
    }
}
