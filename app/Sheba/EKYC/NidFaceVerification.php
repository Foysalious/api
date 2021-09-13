<?php

namespace Sheba\EKYC;

use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;

class NidFaceVerification
{
    private $profileRepo;

    public function __construct(ShebaProfileRepository $profileRepo)
    {
        $this->profileRepo = $profileRepo;
    }

    public function verifiedChanges($data, $profile)
    {
        $data = $this->makeData($data);
        $this->profileRepo->updateRaw($profile, $data);
    }

    private function makeData($data)
    {
        $porichoy_data = $data['porichoy_data'];
        $new_data['name'] = $porichoy_data['name_en'];
        $new_data['bn_name'] = $porichoy_data['name_bn'];
        $new_data['father_name'] = $porichoy_data['father_name'];
        $new_data['mother_name'] = $porichoy_data['mother_name'];
        $new_data['address'] = $porichoy_data['present_address'];
        $new_data['permanent_address'] = $porichoy_data['permanent_address'];
        $new_data['nid_address'] = $porichoy_data['permanent_address'];
        return $new_data;
    }
}