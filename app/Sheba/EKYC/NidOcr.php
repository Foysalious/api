<?php

namespace Sheba\EKYC;

use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;

class NidOcr
{
    private $profileUpdate;
    private $profileRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ShebaProfileRepository $profileRepo)
    {
        $this->profileUpdate = $profileUpdate;
        $this->profileRepo = $profileRepo;
    }

    public function makeProfileAdjustment($profile, $id_front, $id_back, $nid)
    {
        $data = $this->profileUpdate->createDataForNidOcr($id_front, $id_back, $nid);
        return $this->profileRepo->update($profile, $data);
    }
}