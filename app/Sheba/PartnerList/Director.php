<?php namespace Sheba\PartnerList;


use App\Sheba\PartnerList\Builder;

class Director
{
    /** @var Builder */
    private $builder;

    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    public function buildPartnerList()
    {
        $this->builder->checkCategory();
        $this->builder->checkService();
        $this->builder->checkLeave();
        $this->builder->checkVerification();
        $this->builder->checkPartner();
        $this->builder->checkCanAccessMarketPlace();
        $this->builder->withResource();
        $this->builder->withAvgReview();
        $this->builder->runQuery();
        $this->builder->checkOption();
        $this->builder->checkGeoWithinPartnerRadius();
        $this->builder->checkPartnerCreditLimit();
        $this->builder->checkDailyOrderLimit();
        $this->builder->checkPartnerHasResource();
        $this->builder->removeShebaHelpDesk();
    }

    public function buildPartnerListForOrderPlacement()
    {
        $this->builder->checkCategory();
        $this->builder->checkService();
        $this->builder->checkLeave();
        $this->builder->checkPartnerVerification();
        $this->builder->checkPartner();
        $this->builder->checkCanAccessMarketPlace();
        $this->builder->withResource();
        $this->builder->withAvgReview();
        $this->builder->withService();
        $this->builder->withTotalCompletedOrder();
        $this->builder->runQuery();
        $this->builder->checkOption();
        $this->builder->checkGeoWithinPartnerRadius();
        $this->builder->checkPartnerCreditLimit();
        $this->builder->checkPartnerDailyOrderLimit();
        $this->builder->checkPartnerHasResource();
        $this->builder->checkPartnerAvailability();
        $this->builder->removeShebaHelpDesk();
        $this->builder->removeUnavailablePartners();

    }

}