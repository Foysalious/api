<?php namespace Sheba\Order;

use App\Exceptions\NotAvailableException;
use App\Models\Resource;
use Sheba\Jobs\PreferredTime;
use Sheba\Location\Geo;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;

class OrderCreateRequestPolicy
{
    protected $geo;
    protected $partnerListBuilder;
    protected $partnerListDirector;
    protected $services;
    protected $date;
    protected $time;
    protected $partnerId;
    protected $resource;
    protected $serviceRequestObject;

    public function __construct(PartnerListBuilder $partnerListBuilder, Director $partnerListDirector)
    {
        $this->partnerListBuilder = $partnerListBuilder;
        $this->partnerListDirector = $partnerListDirector;
    }

    /**
     * @param Geo $geo
     * @return OrderCreateRequestPolicy
     */
    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    /**
     * @param array $serviceRequestObject
     * @return OrderCreateRequestPolicy
     */
    public function setServiceRequestObject(array $serviceRequestObject)
    {
        $this->serviceRequestObject = $serviceRequestObject;
        return $this;
    }

    /**
     * @param $date
     * @return OrderCreateRequestPolicy
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $time
     * @return OrderCreateRequestPolicy
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @param $id
     * @return OrderCreateRequestPolicy
     */
    public function setPartnerId($id)
    {
        $this->partnerId = $id;
        return $this;
    }

    /**
     * @param Resource $resource
     * @return OrderCreateRequestPolicy
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return bool
     * @throws NotAvailableException
     */
    public function canCreate()
    {
        if (!$this->checkPartner()) throw new NotAvailableException('Partner Not Available', 403);
        if ($this->resource && !$this->checkResource())  throw new NotAvailableException('Resource Not Available', 403);
        return true;
    }

    /**
     * @return bool
     */
    private function checkPartner()
    {
        $this->partnerListBuilder->setGeo($this->geo)->setServiceRequestObjectArray($this->serviceRequestObject)->setScheduleTime($this->time)->setScheduleDate($this->date);
        $this->partnerListBuilder->setPartnerIds([$this->partnerId]);
        $this->partnerListDirector->setBuilder($this->partnerListBuilder);
        $this->partnerListDirector->buildPartnerListForOrderPlacementAdmin();
        $partners = $this->partnerListBuilder->get();
        if ($partners->first()) return true;
        return false;
    }

    /**
     * @return bool
     */
    private function checkResource()
    {
        $preferred_time = new PreferredTime($this->time);
        return scheduler($this->resource)->isAvailableForCategory($this->date, $preferred_time->getStartString(), $this->serviceRequestObject[0]->getCategory());

    }
}