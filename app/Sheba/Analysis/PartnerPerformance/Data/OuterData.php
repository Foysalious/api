<?php namespace Sheba\Analysis\PartnerPerformance\Data;

class OuterData
{
    /** @var InnerData */
    private $innerData;
    private $previous;

    /**
     * @param mixed $previous
     * @return OuterData
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
        return $this;
    }

    /**
     * @param mixed $innerData
     * @return OuterData
     */
    public function setInnerData($innerData)
    {
        $this->innerData = $innerData;
        return $this;
    }

    public function getRate()
    {
        return $this->innerData->getFormattedRate();
    }

    public function getTotal()
    {
        return $this->innerData->getFormattedValue();
    }

    public function toArray()
    {
        /** @var InnerData $last */
        $last = end($this->previous)['data'];

        return [
            'total' => $this->getTotal(),
            'rate' => $this->getRate(),
            'last_rate' => $last->getFormattedRate(),
            'is_improved' => $last->getRate() <= $this->getRate() ? true : false,
            'last_rate_difference' => abs($this->getRate() - $last->getRate()),
            'previous' => $this->formatPrevious($this->previous)
        ];
    }

    private function formatPrevious($previous)
    {
        foreach ($previous as &$item) {
            $item['value'] = $item['data']->getFormattedValue();
            $item['rate'] = $item['data']->getFormattedRate();
            unset($item['data']);
        }
        return $previous;
    }
}