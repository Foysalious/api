<?php namespace Sheba\Analysis\PartnerPerformance\Calculator;

use Sheba\Analysis\PartnerPerformance\PartnerPerformance;

class StatDbWrapper extends PartnerPerformance
{
    protected function get()
    {
        try {
            return collect([]);
        } catch (\Exception $e) {
            return $this->next->get();
        }
    }
}