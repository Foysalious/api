<?php namespace Sheba\Pos\Discount\DTO\Params;

class Order extends SetParams
{
    private $originalAmount;
    private $isPercentage;

    /**
     * @param $original_amount
     * @return $this
     */
    public function setOriginalAmount($original_amount)
    {
        $this->originalAmount = $original_amount;
        return $this;
    }

    /**
     * @param $is_percentage
     * @return $this
     */
    public function setIsPercentage($is_percentage)
    {
        $this->isPercentage = $is_percentage;
        return $this;
    }

    public function getData()
    {
        return [
            'type' => $this->type,
            'amount' => $this->getApplicableAmount(),
            'original_amount' => $this->originalAmount,
            'is_percentage' => $this->isPercentage,
            'sheba_contribution' => 0.00,
            'partner_contribution' => 100.00
        ];
    }

    private function getApplicableAmount()
    {
        return $this->isPercentage ? (($this->originalAmount / 100) * $this->order->getTotalBill()) : $this->originalAmount;
    }
}