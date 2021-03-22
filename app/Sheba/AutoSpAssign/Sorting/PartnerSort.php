<?php namespace Sheba\AutoSpAssign\Sorting;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Strategy\Strategy;

class PartnerSort
{
    /** @var Strategy */
    private $strategy;
    private $categoryId;

    /**
     * @param Strategy $strategy
     * @return $this
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @param $category_id
     * @return $this
     */
    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]
     */
    public function sort($partners)
    {
        return $this->strategy->setCategoryId($this->categoryId)->sort($partners);
    }
}
