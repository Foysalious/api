<?php

namespace Sheba\EKYC;

use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Illuminate\Http\Request;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;

class NidOcr
{
    private $profileUpdate;
    private $profileRepo;
    private $profileNIDSubmissionRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ShebaProfileRepository $profileRepo, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo)
    {
        $this->profileUpdate = $profileUpdate;
        $this->profileRepo = $profileRepo;
        $this->profileNIDSubmissionRepo = $profileNIDSubmissionRepo;
    }

    public function makeProfileAdjustment($profile, $id_front, $id_back, $nid)
    {
        $data = $this->profileUpdate->createDataForNidOcr($id_front, $id_back, $nid);
        return $this->profileRepo->update($profile, $data);
    }

    public function formatToData(Request $request, $user_agent)
    {
        $data['id_front'] = $request->file('id_front');
        $data['id_back'] = $request->file('id_back');
        $data['user_agent'] = $user_agent;
        return $data;
    }

    public function storeData($request, $nid_ocr_data, $nid_no, $user_agent, $business_name = "sManager", $feature_name = "NID Verification")
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $ocrData = $nid_ocr_data['data'];
        $ocrData = json_encode(array_except($ocrData, ['id_front_name', 'id_back_name']));
        $log = "NID submitted by the user";

        $data = [
            'profile_id' => $profile_id,
            "nid_no"     => $nid_no,
            'submitted_by' => $submitted_by,
            'nid_ocr_data' => $ocrData,
            'verification_status' => Statics::INCOMPLETE,
            'user_agent' => $user_agent,
            'log' => $log,
            'business_name' => $business_name,
            'feature_name' => $feature_name,
        ];

        $this->profileNIDSubmissionRepo->create($data);
    }
}
