<?php

namespace Sheba\EKYC;

use App\Repositories\ResourceRepository;
use Carbon\Carbon;
use Sheba\Repositories\AffiliateRepository;
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
        if(isset($profile->resource)) (new ResourceRepository($profile->resource))->update([
            "status" => 'verified',
            "is_verified" => 1,
            "verified_at" => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function unverifiedChanges($profile) {
        if(isset($profile->resource)) (new ResourceRepository($profile->resource))->update([
            "status" => 'rejected'
        ]);
    }

    public function beforePorichoyCallChanges($profile)
    {
        $this->profileRepo->increase_verification_request_count($profile);
        if(isset($profile->resource)) (new ResourceRepository($profile->resource))->setToPendingStatus();
        elseif(isset($profile->affiliate)) (new AffiliateRepository())->updateVerificationStatus($profile->affiliate);
    }

    private function makeData($data): array
    {
        $porichoy_data = $data['porichoy_data'];
        $new_data['name'] = $porichoy_data['name_en'];
        $new_data['bn_name'] = $porichoy_data['name_bn'];
        $new_data['father_name'] = $porichoy_data['father_name'];
        $new_data['mother_name'] = $porichoy_data['mother_name'];
        $new_data['address'] = $porichoy_data['present_address'];
        $new_data['permanent_address'] = $porichoy_data['permanent_address'];
        $new_data['nid_address'] = $porichoy_data['permanent_address'];
        $new_data['nid_verified'] = 1;
        $new_data['nid_verification_date'] = Carbon::now()->toDateTimeString();
        return $new_data;
    }
}
