<?php

namespace Sheba\EKYC;

use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use App\Sheba\DigitalKYC\Partner\ResourceUpdateRepository;
use Illuminate\Http\Request;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;
use Sheba\Repositories\ResourceRepository as ShebaResourceRepository;

class NidOcr
{
    private $profileUpdate;
    private $resourceUpdate;
    private $profileRepo;
    private $resourceRepo;
    private $profileNIDSubmissionRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ResourceUpdateRepository $resourceUpdate, ShebaProfileRepository $profileRepo,
                                ShebaResourceRepository $resourceRepo, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo)
    {
        $this->profileUpdate = $profileUpdate;
        $this->resourceUpdate = $resourceUpdate;
        $this->profileRepo = $profileRepo;
        $this->resourceRepo = $resourceRepo;
        $this->profileNIDSubmissionRepo = $profileNIDSubmissionRepo;
    }

    public function makeProfileAdjustment($profile, $id_front, $id_back, $nid)
    {
        $data = $this->profileUpdate->createDataForNidOcr($id_front, $id_back, $nid);
        return $this->profileRepo->update($profile, $data);
    }

    public function makeResourceAdjustment($resource, $father_name, $mother_name, $spouse_name, $nid_no)
    {
        $data = $this->resourceUpdate->createDataForNidOcr($father_name, $mother_name, $spouse_name, $nid_no);
        return $this->resourceRepo->update($resource, $data);
    }

    public function formatToData(Request $request)
    {
        $data['id_front'] = $request->file('id_front');
        $data['id_back'] = $request->file('id_back');
        return $data;    }

    public function storeData($request, $nidOcrData, $nid_no, $business_name = "sManager", $feature_name = "NID Verification")
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $ocrData = $nidOcrData['data'];
        $ocrData = json_encode(array_except($ocrData, ['id_front_image', 'id_back_image', 'id_front_name', 'id_back_name']));
        $log = "NID submitted by the user";

        $data = [
            'profile_id' => $profile_id,
            "nid_no"     => $nid_no,
            'submitted_by' => $submitted_by,
            'nid_ocr_data' => $ocrData,
            'business_name' => $business_name,
            'feature_name' => $feature_name,
            'log' => $log
        ];

        $this->profileNIDSubmissionRepo->create($data);
    }
}
