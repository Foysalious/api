<?php namespace Sheba\Reports\Customer;

class CustomerNormalData extends CustomerData
{
    protected function calculateTimeFrame()
    {
        return (!$this->request->has('is_lifetime'))
            ? ['start_date' => $this->request->start_date, 'end_date' => $this->request->end_date]
            : null;
    }
}