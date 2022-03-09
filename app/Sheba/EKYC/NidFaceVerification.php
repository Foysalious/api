<?php

namespace Sheba\EKYC;

use App\Models\Partner;
use App\Models\Resource;
use App\Repositories\ResourceRepository;
use Carbon\Carbon;
use Exception;
use Intervention\Image\Facades\Image;
use Sheba\Dal\ProfileNIDSubmissionLog\Model as ProfileNIDSubmissionLog;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\ModificationFields;
use Sheba\Repositories\AffiliateRepository;
use App\Repositories\FileRepository;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Sheba\Repositories\ProfileRepository;

class NidFaceVerification
{
    use ModificationFields;

    private $profileRepo;
    private $fileRepo;

    public function __construct(ProfileUpdateRepository $profileUpdate, ProfileRepository $profileRepo, FileRepository $file_repository)
    {
        $this->profileRepo = $profileRepo;
        $this->fileRepo = $file_repository;
    }

    /**
     * @param $data
     * @param $profile
     * @throws Exception
     */
    public function verifiedChanges($data, $profile)
    {
        $data = $this->makeData($data);
        $this->profileRepo->updateRaw($profile, $data);
        if(isset($profile->resource)) {
            $resourceRepo = (new ResourceRepository($profile->resource));
            $count = $resourceRepo->checkDuplicateNIDInResource($data['nid_no']);
            if($count > 0) throw new EKycException("NID already exist in another resource");
            $resourceRepo->update($this->withUpdateModificationField([
                'name_bn' => $data['name_bn'] ?? 'N/A',
                'father_name' => $data['father_name'] ?? 'N/A',
                'mother_name' => $data['mother_name'],
                'spouse_name' => $data['spouse_name'] ?? 'N/A',
                'nid_no' => $data['nid_no'],
                "status" => 'verified',
                "is_verified" => 1,
                "verified_at" => Carbon::now()->toDateTimeString(),
            ]));

            $resourceRepo->storeStatusUpdateLog('verified', 'ekyc_verified', "status changed to verified through ekyc");
        }
    }

    public function unverifiedChanges($profile) {
        $this->profileRepo->updateRaw($profile, [
            'nid_verified' => 0,
            'nid_verification_date' => null
        ]);
        if(isset($profile->resource)) {
            $resourceRepo = (new ResourceRepository($profile->resource));
            $resourceRepo->update([
                "status" => 'rejected',
                "is_verified" => 0,
                "verified_at" => null
            ]);

            $resourceRepo->storeStatusUpdateLog('rejected', 'ekyc_rejected', "status changed to rejected through ekyc");
        }
    }

    public function beforePorichoyCallChanges($profile, $avatar)
    {
        $this->profileRepo->increase_verification_request_count($profile);
        if($avatar instanceof Partner) {
            (new ResourceRepository($profile->resource))->setToPendingStatus();
        } else {
            (new AffiliateRepository())->updateVerificationStatusToPending($profile->affiliate);
        }
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
        $image = $request->person_photo;
        $image=explode(",",$image);
        $image=base64_decode($image['1']);
        $png_url = "user-".time().".jpg";
        $path = public_path("temp/$png_url");
        Image::make($image)->save($path);
        /** @var ProfileRepository $profile_repo */
        $profile_repo  = app()->make(ProfileRepository::class);
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id;
        $saveProPic = $profile_repo->saveProPic($path, $filename);
        unlink($path);
        return $saveProPic;
    }

    /**
     * @param $request
     * @param $userAgent
     * @param $photoLink
     * @return array
     */
    public function formatToData($request, $userAgent, $photoLink): array
    {
        $data['nid'] = $request->nid;
        $data['pro_pic'] = $request->person_photo;
        $data['dob'] = $request->dob;
        $data['selfie_photo'] = $photoLink;
        $data['user_agent'] = $userAgent;
        return $data;
    }

    public function storeResubmitData($faceVerificationData, $profileNIDSubmissionRepo)
    {
        $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage', 'reject_reason']);
        $faceVerify = json_encode($faceVerify);

        $profileNIDSubmissionRepo->update([
            'porichy_data' => $faceVerify,
            "verification_status" => ($faceVerificationData['data']['status'] === "verified" || $faceVerificationData['data']['status'] === "already_verified") ? "approved" : "rejected",
            "rejection_reasons" => $faceVerificationData['data']['reject_reason'] ? json_encode($faceVerificationData['data']['reject_reason']) : null,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);

    }

    public function storeData($request, $avatar, $faceVerificationData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = $avatar instanceof Partner ? get_class($request->auth_user->getResource()) : get_class($request->auth_user->getAffiliate());
        $faceVerify = null;
        $verificationStatus = "rejected";
        $rejectReasons = "Invalid Data";
        if($faceVerificationData) {
            $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage', 'reject_reason']);
            $faceVerify = json_encode($faceVerify);
            $verificationStatus = ($faceVerificationData['data']['status'] === "verified"
                || $faceVerificationData['data']['status'] === "already_verified") ? "approved" : "rejected";
            $rejectReasons = $faceVerificationData['data']['reject_reason'] ? json_encode($faceVerificationData['data']['reject_reason']) : null;
        }

        $requestedData = [
            'nid' => $request->nid,
            'dob' => $request->dob,
        ];
        $requestedData = json_encode($requestedData);

        $porichoyNIDSubmission = $profileNIDSubmissionRepo->where('profile_id', $profile_id)
            ->where('submitted_by', $submitted_by)
            ->where('nid_no', $request->nid)
            ->orderBy('id', 'desc')->first();

        if ($porichoyNIDSubmission) {
            $porichoyNIDSubmission->update([
                'porichoy_request'    => $requestedData,
                'porichy_data'        => $faceVerify,
                "verification_status" => $verificationStatus,
                "rejection_reasons"   => $rejectReasons,
                'created_at'          => Carbon::now()->toDateTimeString()
            ]);
        }
    }

    /**
     * @param $request
     * @param $avatar
     * @param $profileNIDSubmissionRepo
     */
    public function updateLivelinessCount($request, $avatar, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = $avatar instanceof Partner ? get_class($request->auth_user->getResource()) : get_class($request->auth_user->getAffiliate());

        $porichoyNIDSubmission = $profileNIDSubmissionRepo->where('profile_id', $profile_id)
            ->where('submitted_by', $submitted_by)
            ->where('nid_no', $request->nid)
            ->orderBy('id', 'desc')->first();

        if ($porichoyNIDSubmission) {
            $porichoyNIDSubmission->update([
                'liveliness_complete' => 1
            ]);
        }
    }

    private function makeData($data): array
    {
        $porichoy_data = $data['porichoy_data'];
        $new_data['name'] = $porichoy_data['name_en'];
        $new_data['bn_name'] = $porichoy_data['name_bn'];
        $new_data['father_name'] = $porichoy_data['father_name'];
        $new_data['mother_name'] = $porichoy_data['mother_name'];
        $new_data['spouse_name'] = $porichoy_data['spouse_name'];
        $new_data['address'] = $porichoy_data['present_address'];
        $new_data['permanent_address'] = $porichoy_data['permanent_address'];
        $new_data['nid_address'] = $porichoy_data['permanent_address'];
        $new_data['nid_no'] = $porichoy_data['nid_no'];
        $new_data['nid_verified'] = 1;
        $new_data['nid_verification_date'] = Carbon::now()->toDateTimeString();
        $new_data['dob'] = Carbon::parse($porichoy_data['dob'])->format("Y-m-d");
        return $new_data;
    }
}
