<?php

namespace Sheba\Loan;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Sheba\Dal\PartnerBankLoan\LoanTypes;

class Completion
{
    private $data;
    private $updatedStamps;
    private $flatten;
    private $skipFields;
    private $checkFields
        = [
            "licence_agreement_checked",
            "ipdc_data_agreement_checked",
            "ipdc_cib_agreement_checked"
        ];

    public function __construct(array $data, array $updated_stamps, array $skipFields = [])
    {
        $this->data          = $data;
        $this->updatedStamps = $updated_stamps;
        $this->skipFields    = $skipFields;
    }

    public static function isApplicableForLoan(&$data,$type,$version)
    {
        if (isset($data['nominee_granter'])) {
            $data['nominee'] = $data['nominee_granter'];
        }
        if (isset($data['document']))
            $data['documents'] = $data['document'];

        if ($type == LoanTypes::TERM)
            return (($data['personal']['completion_percentage'] >= 50) && ($data['business']['completion_percentage'] >= 20) && ($data['finance']['completion_percentage'] >= 70) && ($data['nominee']['completion_percentage'] == 100) && ($data['documents']['completion_percentage'] >= 50)) ? 1 : 0;
        if ($type == LoanTypes::MICRO && $version == 2)
            return (($data['personal']['completion_percentage'] == 100) && ($data['business']['completion_percentage'] == 100) && ($data['finance']['completion_percentage'] >= 20) && ($data['documents']['completion_percentage'] >= 80)) ? 1 : 0;
        return 1;
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
            if (!in_array($key,$this->checkFields)){
                if (is_array($value) || $value === true || $value === false || is_int($key)) {
                    continue;
                }
            }
            if (!in_array($key, $this->skipFields)) {
                if ($value !== null) {
                    $filled++;
                }
                $count++;
            }
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
