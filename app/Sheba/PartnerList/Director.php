<?php namespace Sheba\PartnerList;

class Director
{
    /** @var Builder */
    private $builder;
    private $baseQueryFunctions;
    /** @var array */
    private $partnersAfterServiceCondition;
    /** @var array */
    private $partnersAfterLocationCondition;
    /** @var array */
    private $partnersAfterOptionCondition;
    /** @var array */
    private $partnersAfterCreditCondition;
    /** @var array */
    private $partnersAfterOrderLimitCondition;
    /** @var array */
    private $partnersAfterResourceCondition;
    /** @var array */
    private $partnersAfterAvailabilityCondition;

    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    private function setPartnersAfterServiceCondition($partnersAfterServiceCondition)
    {
        $this->partnersAfterServiceCondition = $partnersAfterServiceCondition;
        return $this;
    }


    private function setPartnersAfterLocationCondition($partnersAfterLocationCondition)
    {
        $this->partnersAfterLocationCondition = $partnersAfterLocationCondition;
        return $this;
    }

    private function setPartnersAfterOptionCondition($partnersAfterOptionCondition)
    {
        $this->partnersAfterOptionCondition = $partnersAfterOptionCondition;
        return $this;
    }


    private function setPartnersAfterCreditCondition($partnersAfterCreditCondition)
    {
        $this->partnersAfterCreditCondition = $partnersAfterCreditCondition;
        return $this;
    }


    public function setPartnersAfterOrderLimitCondition($partnersAfterOrderLimitCondition)
    {
        $this->partnersAfterOrderLimitCondition = $partnersAfterOrderLimitCondition;
        return $this;
    }

    private function setPartnersAfterResourceCondition($partnersAfterResourceCondition)
    {
        $this->partnersAfterResourceCondition = $partnersAfterResourceCondition;
        return $this;
    }

    private function setPartnersAfterAvailabilityCondition($partnersAfterAvailabilityCondition)
    {
        $this->partnersAfterAvailabilityCondition = $partnersAfterAvailabilityCondition;
        return $this;
    }

    public function buildPartnerList()
    {
        $this->buildBaseQuery();
        $this->builder->runQuery();
        $this->setPartnersAfterServiceCondition($this->getPartnerIds());
        $this->filterBaseConditions();
    }


    public function buildPartnerListForOrderPlacement()
    {
        $this->buildQueryForOrderPlace();
        $this->buildQueryForPartnerScoring();
        $this->builder->runQuery();
        $this->filterForOrderPlace();
    }

    public function buildPartnerListForAdmin()
    {
        $this->buildBaseQuery();
        $this->builder->withTotalOngoingJobs();
        $this->builder->runQuery();
        $this->setPartnersAfterServiceCondition($this->getPartnerIds());
        $this->filterBaseConditions();
        $this->builder->resolveInfoForAdminPortal();
    }

    public function buildPartnerListForOrderPlacementAdmin()
    {
        $this->buildQueryForOrderPlace();
        $this->buildQueryForPartnerScoring();
        $this->builder->withTotalOngoingJobs();
        $this->builder->runQuery();
        $this->filterForOrderPlace();
        $this->builder->resolveInfoForAdminPortal();
    }


    private function buildBaseQuery()
    {
        $this->baseQueryFunctions = [
            'checkCategory', 'checkService', 'checkLeave', 'checkPartnerVerification', 'checkPartner',
            'checkCanAccessMarketPlace', 'withResource', 'withAvgReview'
        ];
        $this->builder->checkCategory();
        $this->builder->checkService();
        $this->builder->checkLeave();
        $this->builder->checkPartnerVerification();
        $this->builder->checkPartner();
        $this->builder->checkCanAccessMarketPlace();
        $this->builder->withoutShebaHelpDesk();
        $this->builder->withResource();
        $this->builder->withAvgReview();
    }

    private function filterBaseConditions()
    {
        $this->builder->checkOption();
        $this->setPartnersAfterOptionCondition($this->getPartnerIds());

        $this->builder->checkGeoWithinPartnerRadius();
        $this->setPartnersAfterLocationCondition($this->getPartnerIds());

        $this->builder->checkPartnerCreditLimit();
        $this->setPartnersAfterCreditCondition($this->getPartnerIds());

        $this->builder->checkPartnerDailyOrderLimit();
        $this->setPartnersAfterOrderLimitCondition($this->getPartnerIds());

        $this->builder->checkPartnerHasResource();
        $this->setPartnersAfterResourceCondition($this->getPartnerIds());

        $this->builder->resolvePartnerSortingParameters();
        $this->builder->sortPartners();
    }

    private function buildQueryForOrderPlace()
    {
        $this->buildBaseQuery();
        $this->buildQueryForPartnerScoring();
        $this->builder->checkPartnersToIgnore();
    }

    private function buildQueryForPartnerScoring()
    {
        $this->builder->withService();
        $this->builder->withSubscriptionPackage();
        $this->builder->withTotalCompletedOrder();
    }

    private function filterForOrderPlace()
    {
        $this->filterBaseConditions();
        $this->builder->checkPartnerAvailability();
        $this->builder->removeUnavailablePartners();
        $this->setPartnersAfterAvailabilityCondition($this->getPartnerIds());
    }

    public function getPartnerIdsAfterEachCondition()
    {
        return [
            'services' => $this->partnersAfterServiceCondition ? $this->partnersAfterServiceCondition : [],
            'location' => $this->partnersAfterLocationCondition ? $this->partnersAfterLocationCondition : [],
            'option' => $this->partnersAfterOptionCondition ? $this->partnersAfterOptionCondition : [],
            'credit' => $this->partnersAfterCreditCondition ? $this->partnersAfterCreditCondition : [],
            'order_limit' => $this->partnersAfterOrderLimitCondition ? $this->partnersAfterOrderLimitCondition : [],
            'resource' => $this->partnersAfterResourceCondition ? $this->partnersAfterResourceCondition : [],
            'availability' => $this->partnersAfterAvailabilityCondition ? $this->partnersAfterAvailabilityCondition : [],
        ];
    }

    private function getPartnerIds()
    {
        $partners = $this->builder->get();
        if (count($partners) == 0) return [];
        return $partners->pluck('id')->values()->all();
    }
}
