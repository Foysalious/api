<?php

namespace Sheba\EKYC;

use App\Repositories\ResourceRepository;
use Carbon\Carbon;
use Sheba\Repositories\AffiliateRepository;
use App\Http\Requests\Request;
use App\Repositories\FileRepository;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Sheba\Repositories\ProfileRepository;

class NidFaceVerification
{
    private $profileUpdate;
    private $profileRepo;
    private $fileRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ProfileRepository $profileRepo, FileRepository $file_repository)
    {
        $this->profileUpdate = $profileUpdate;
        $this->profileRepo = $profileRepo;
        $this->fileRepo = $file_repository;
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
        $this->profileRepo->updateRaw($profile, [
            'nid_verified' => 0,
            'nid_verification_date' => null
        ]);
        if(isset($profile->resource)) (new ResourceRepository($profile->resource))->update([
            "status" => 'rejected',
            "is_verified" => 0,
            "verified_at" => null
        ]);
    }

    public function beforePorichoyCallChanges($profile)
    {
        $this->profileRepo->increase_verification_request_count($profile);
        if(isset($profile->resource)) (new ResourceRepository($profile->resource))->setToPendingStatus();
        elseif(isset($profile->affiliate)) (new AffiliateRepository())->updateVerificationStatus($profile->affiliate);
    }

    /**
     * @param $photoLink
     * @param $profile
     * @param $nid
     */
    public function makeProfileAdjustment($photoLink, $profile, $nid)
    {
        if (basename($profile->pro_pic) != 'default.jpg') {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepo->deleteFileFromCDN($filename);
        }
        $profile->pro_pic = $photoLink;
        $profile->nid_no = $nid;
        $profile->save();
    }

    public function getPersonPhotoLink($request, $profile): string
    {
        $photo = $request->file('person_photo');
        /** @var ProfileRepository $profile_repo */
        $profile_repo  = app()->make(ProfileRepository::class);
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id;
        return $profile_repo->saveProPic($photo, $filename);
    }

    /**
     * @param $request
     * @param $photoLink
     * @return array
     */
    public function formatToData($request, $photoLink): array
    {
        $data['nid'] = $request->nid;
        $data['pro_pic'] = $request->file('person_photo');
        $data['dob'] = $request->dob;
        $data['selfie_photo'] = $photoLink;
        return $data;
    }

    public function storeData($request, $faceVerificationData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage', 'reject_reason']);
        $faceVerify = json_encode($faceVerify);

        $requestedData = [
            'nid' => $request->nid,
            'dob' => $request->dob,
        ];
        $requestedData = json_encode($requestedData);

        $porichoyNIDSubmission = $profileNIDSubmissionRepo->where('profile_id', $profile_id)
            ->where('submitted_by', $submitted_by)
            ->where('nid_no', $request->nid)
            ->orderBy('id', 'desc')->first();

        $porichoyNIDSubmission->update([
            'porichoy_request'    => $requestedData,
            'porichy_data'        => $faceVerify,
            "verification_status" => ($faceVerificationData['data']['status'] === "verified" || $faceVerificationData['data']['status'] === "already_verified") ? "approved" : "rejected",
            "rejection_reasons"   => $faceVerificationData['data']['reject_reason'] ?? null,
            'created_at'          => Carbon::now()->toDateTimeString()
        ]);

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
        $new_data['dob'] = Carbon::parse($porichoy_data['dob'])->format("Y-m-d");
        return $new_data;
    }
}
