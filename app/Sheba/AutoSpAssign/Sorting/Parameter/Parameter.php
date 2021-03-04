<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


use Sheba\AutoSpAssign\EligiblePartner;

abstract class Parameter
{
    /** @var EligiblePartner */
    protected $partner;
    protected $maxValue;
    protected $minValue;
    protected $categoryId;

    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;
        return $this;
    }

    public function setPartner(EligiblePartner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $category_id
     * @return Parameter
     */
    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    /**
     * Weighted score in scale of 100
     *
     * @return double
     */
    public function getScore()
    {
        return $this->getValueForPartner() * $this->getWeightInScaleOf1();
    }

    /**
     * Weight in scale of 1
     *
     * @return double
     */
    public function getWeightInScaleOf1()
    {
        return $this->getWeight() / 100;
    }

    /**
     * Weight in scale of 100
     *
     * @return int
     */
    abstract protected function getWeight();

    /**
     * Value in scale of 100
     *
     * @return int
     */
    abstract protected function getValueForPartner();

}
