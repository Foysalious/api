<?php namespace Sheba\Order;

use App\Models\Resource;
use Sheba\Jobs\PreferredTime;
use Sheba\Location\Geo;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;

class CheckAvailabilityForOrderPlace
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

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function setServiceRequestObject(array $serviceRequestObject)
    {
        $this->serviceRequestObject = $serviceRequestObject;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function setPartnerId($id)
    {
        $this->partnerId = $id;
        return $this;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function checkPartner()
    {
        $this->partnerListBuilder->setGeo($this->geo)->setServiceRequestObjectArray($this->serviceRequestObject)->setScheduleTime($this->time)->setScheduleDate($this->date);
        $this->partnerListBuilder->setPartnerIds([$this->partnerId]);
        $this->partnerListDirector->setBuilder($this->partnerListBuilder);
        $this->partnerListDirector->buildPartnerListForOrderPlacementAdmin();
        $partners = $this->partnerListBuilder->get();
        if ($partners->first()) return true;
        return false;
    }

    public function checkResource()
    {
        $preferred_time = new PreferredTime($this->time);
        return scheduler($this->resource)->isAvailableForCategory($this->date, $preferred_time->getStartString(), $this->serviceRequestObject[0]->getCategory());

    }
}