<?php

namespace Sheba\Loan;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Completion
{
    private $data;
    private $updatedStamps;
    private $flatten;

    public function __construct(array $data, array $updated_stamps)
    {
        $this->data          = $data;
        $this->updatedStamps = $updated_stamps;
    }

    public static function isApplicableForLoan(&$data)
    {
        if (isset($data['nominee_granter'])) {
            $data['nominee'] = $data['nominee_granter'];
        }
        if (isset($data['document']))
            $data['documents'] = $data['document'];
        return (($data['personal']['completion_percentage'] >= 50) && ($data['business']['completion_percentage'] >= 20) && ($data['finance']['completion_percentage'] >= 70) && ($data['nominee']['completion_percentage'] == 100) && ($data['documents']['completion_percentage'] >= 50)) ? 1 : 0;
    }

    public function get()
    {
        return [
            'completion_percentage' => round($this->flatten()->percentage()),
            'last_updated'          => $this->lastUpdated()
        ];
    }

    private function percentage()
    {
        $count  = 0;
        $filled = 0;
        foreach ($this->flatten as $key => $value) {
            if (is_array($value) || $value === true || $value === false || $key == 'extra_images') {
                continue;
            }
            if ($value !== null) {
                $filled++;
            }
            $count++;
        }
        return ($filled / $count) * 100;
    }

    private function flatten()
    {
        $this->flatten = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->data));
        return $this;
    }

    private function lastUpdated()
    {
        $max     = max($this->updatedStamps);
        $updated = null;
        if ($max) {
            $updated = getDayName($max);
        }
        return $updated;
    }

}
