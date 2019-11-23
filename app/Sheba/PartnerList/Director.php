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

    /**
     * The Director can construct several product variations using the same
     * building steps.
     */
    public function buildPartnerList(): void
    {
        $this->builder->checkCategory();
        $this->builder->checkService();
        $this->builder->checkLeave();
        $this->builder->checkVerification();
        $this->builder->checkPartner();
        $this->builder->checkCanAccessMarketPlace();
        $this->builder->withResource();
        $this->builder->WithAvgReview();
        $this->builder->runQuery();
        $this->builder->checkGeoWithinPartnerRadius();
        $this->builder->checkPartnerCreditLimit();
        $this->builder->checkPartnerHasResource();
        $this->builder->removeShebaHelpDesk();
    }

}