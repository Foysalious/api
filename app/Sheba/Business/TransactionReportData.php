<?php namespace Sheba\Business;


use Sheba\Helpers\TimeFrame;

class TransactionReportData
{
    /** @var TimeFrame */
    private $timeFrame;

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function get()
    {
        return [];
    }
}
