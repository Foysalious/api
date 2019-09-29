<?php namespace Sheba\Jobs;

use Carbon\Carbon;

class PreferredTime
{
    /** @var Carbon */
    private $start;
    /** @var Carbon */
    private $end;

    const FORMAT = 'G:i:s';
    const READABLE_FORMAT = 'g:i A';

    public function __construct($time_string)
    {
        $preferred_time = array_map(function($time) {
            return Carbon::parse($time);
        }, explode('-', $time_string));
        $this->start = $preferred_time[0];
        $this->end = $preferred_time[1];
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getStartString()
    {
        return $this->start->format(self::FORMAT);
    }

    public function getEndString()
    {
        return $this->end->format(self::FORMAT);
    }

    public function toString()
    {
        return $this->getStartString() . '-' . $this->getEndString();
    }

    public function toReadableString()
    {
        return $this->start->format(self::READABLE_FORMAT) . '-' . $this->end->format(self::READABLE_FORMAT);
    }
}
