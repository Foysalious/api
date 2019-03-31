<?php namespace Sheba\Reports\Complain\Getters;

use App\Models\ComplainReport;

class GeneratedDataGetter extends Getter
{
    /**
     * @param ComplainReport $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setComplainReport($item)->getForView();
    }

    protected function getQuery()
    {
        return ComplainReport::query();
    }

    protected function mapCustomerFirstOrder($data)
    {
        return $data;
    }
}