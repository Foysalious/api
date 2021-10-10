<?php namespace Sheba\AutoSpAssign;


use Sheba\AutoSpAssign\Sorting\PartnerSort;
use Sheba\AutoSpAssign\Sorting\Strategy\Strategy;

class Sorter
{
    /** @var Strategy */
    private $strategy;
    /** @var array */
    private $partnerIds;
    private $categoryId;
    /** @var Finder */
    private $finder;
    /** @var PartnerSort */
    private $sort;


    /**
     * @param $strategy
     * @return $this
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @param array $partner_ids
     * @return Sorter
     */
    public function setPartnerIds($partner_ids)
    {
        $this->partnerIds = $partner_ids;
        return $this;
    }

    /**
     * @param array $category_id
     * @return Sorter
     */
    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    public function __construct(Finder $finder, PartnerSort $sort)
    {
        $this->finder = $finder;
        $this->sort = $sort;
    }

    /**
     * @return EligiblePartner[]
     */
    public function getSortedPartners()
    {
        $eligible_partners = $this->finder->setPartnerIds($this->partnerIds)->setCategoryId($this->categoryId)->find();
        return $this->sort->setStrategy($this->strategy)->setCategoryId($this->categoryId)->sort($eligible_partners);
    }
}
