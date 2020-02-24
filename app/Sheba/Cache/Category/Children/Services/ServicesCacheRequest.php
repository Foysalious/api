<?php namespace Sheba\Cache\Category\Children\Services;


use Sheba\Cache\CacheName;
use Sheba\Cache\CacheRequest;

class ServicesCacheRequest implements CacheRequest
{
    private $locationId;
    private $categoryId;
    private $lat;
    private $lng;
    private $isBusiness;
    private $isForBackend;
    private $isB2b;
    private $isDdn;
    private $offset;
    private $limit;
    private $scope;
    private $serviceId;


    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($categoryId)
    {
        $this->categoryId = (int)$categoryId;
        return $this;
    }


    public function getLocationId()
    {
        return $this->locationId;
    }

    public function setLocationId($locationId)
    {
        $this->locationId = (int)$locationId;
        return $this;
    }


    public function getLat()
    {
        return $this->lat;
    }


    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }


    public function getLng()
    {
        return $this->lng;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    public function getIsBusiness()
    {
        return $this->isBusiness;
    }


    public function setIsBusiness($isBusiness)
    {
        $this->isBusiness = (int)$isBusiness;
        return $this;
    }


    public function getIsForBackend()
    {
        return $this->isForBackend;
    }

    public function setIsForBackend($isForBackend)
    {
        $this->isForBackend = (int)$isForBackend;
        return $this;
    }


    public function getIsB2b()
    {
        return $this->isB2b;
    }


    public function setIsB2b($isB2b)
    {
        $this->isB2b = (int)$isB2b;
        return $this;
    }

    public function getIsDdn()
    {
        return $this->isDdn;
    }


    public function setIsDdn($isDdn)
    {
        $this->isDdn = (int)$isDdn;
        return $this;
    }


    public function getOffset()
    {
        return $this->offset;
    }


    public function setOffset($offset)
    {
        $this->offset = (int)$offset;
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }


    public function getScope()
    {
        return $this->scope;
    }


    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }


    public function getServiceId()
    {
        return $this->serviceId;
    }


    public function setServiceId($serviceId)
    {
        $this->serviceId = (int)$serviceId;
        return $this;
    }


    public function getFactoryName()
    {
        return CacheName::SECONDARY_CATEGORY_SERVICES;
    }
}