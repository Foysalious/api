<?php namespace Sheba\Analysis\PartnerPerformance\Calculators;

use Sheba\Analysis\PartnerPerformance\PartnerPerformance;

class StatDbWrapper extends PartnerPerformance
{
    protected function get()
    {
        try {
            throw new \Exception();
        } catch (\Exception $e) {
            return $this->next->get();
        }
    }
}