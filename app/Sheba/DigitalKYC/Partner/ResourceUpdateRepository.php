<?php namespace App\Sheba\DigitalKYC\Partner;

class ResourceUpdateRepository
{
    public function createDataForNidOcr($father_name, $mother_name, $spouse_name, $nid): array
    {
        $data['father_name'] = $father_name != "none" ? $father_name : "N/A";
        $data['mother_name'] = $mother_name;
        $data['spouse_name'] = $spouse_name != "none" ? $spouse_name : "N/A";
        $data['nid_no'] = $nid;
        return $data;
    }
}