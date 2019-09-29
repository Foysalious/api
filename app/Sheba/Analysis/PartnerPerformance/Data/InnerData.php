<?php namespace Sheba\Analysis\PartnerPerformance\Data;

class InnerData
{
    private $value;
    private $denominator;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getFormattedValue()
    {
        return $this->value < 0 ? 0 : $this->value;
    }

    /**
     * @param mixed $value
     * @return InnerData
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->denominator ? ($this->value / $this->denominator) : 0;
    }

    public function getFormattedRate()
    {
        $rate = $this->getRate();
        if ($rate < 0) return 0;
        if ($rate > 1) return 1;
        return $rate;
    }

    /**
     * @param mixed $denominator
     * @return InnerData
     */
    public function setDenominator($denominator)
    {
        $this->denominator = $denominator;
        return $this;
    }

    public function getArray()
    {
        return [
            'value' => $this->value,
            'rate' => $this->getFormattedRate()
        ];
    }
}