<?php

namespace Sheba\EKYC;

use App\Http\Requests\Request;
use App\Repositories\FileRepository;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Carbon\Carbon;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;

class NidFaceVerification
{
    private $profileUpdate;
    private $profileRepo;
    private $fileRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ShebaProfileRepository $profileRepo, FileRepository $file_repository)
    {
        $this->profileUpdate = $profileUpdate;
        $this->profileRepo = $profileRepo;
        $this->fileRepo = $file_repository;
    }

    public function verifiedChanges($data, $profile)
    {
        $data = $this->makeData($data);
        $this->profileRepo->updateRaw($profile, $data);
    }

    public function formatToData($request)
    {
        $data['nid'] = $request->nid;
        $data['pro_pic'] = $request->person_photo;
        $data['dob'] = $request->dob;
        return $data;
    }

//    public function makeProfileAdjustment($profile, $person_photo)
//    {
//        $data = $this->profileUpdate->createDataForPorichoyEkyc($person_photo);
//        return $this->profileRepo->update($profile, $data);
//    }

    public function storeData($request, $faceVerificationData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage']);
        $faceVerify = json_encode($faceVerify);

        $requestedData = [
            'nid' => $request->nid,
            'person_photo' => $request->person_photo,
            'dob' => $request->dob,
        ];
        $requestedData = json_encode($requestedData);

        $porichoyNIDSubmission = $profileNIDSubmissionRepo->where('profile_id', $profile_id)
            ->where('submitted_by', $submitted_by)
            ->where('nid_no', $request->nid)
            ->orderBy('id', 'desc')->first();

        $porichoyNIDSubmission->update(['porichoy_request' => $requestedData, 'porichy_data' => $faceVerify, 'created_at' => Carbon::now()->toDateTimeString()]);

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