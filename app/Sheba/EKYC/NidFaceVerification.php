<?php

namespace Sheba\EKYC;

use App\Repositories\ResourceRepository;
use Carbon\Carbon;
use Sheba\Repositories\AffiliateRepository;
use App\Http\Requests\Request;
use App\Repositories\FileRepository;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use Sheba\Repositories\ProfileRepository;
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

    /**
     * @param $request
     * @param $profile
     */
    public function makeProfileAdjustment($request, $profile)
    {
        $photo = $request->file('person_photo');
        /** @var ProfileRepository $profile_repo */
        $profile_repo  = app()->make(ProfileRepository::class);
        if (basename($profile->pro_pic) != 'default.jpg') {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepo->deleteFileFromCDN($filename);
        }
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id;
        $picture_link = $profile_repo->saveProPic($photo, $filename);
        $profile->pro_pic = $picture_link;
        $profile->nid_no = $request->nid;
        $profile->save();
    }

    /**
     * @param $request
     * @return array
     */
    public function formatToData($request)
    {
        $data['nid'] = $request->nid;
        $data['pro_pic'] = $request->file('person_photo');
        $data['dob'] = $request->dob;
        return $data;
    }

    public function storeData($request, $faceVerificationData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage']);
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

        $porichoyNIDSubmission->update(['porichoy_request' => $requestedData, 'porichy_data' => $faceVerify, 'created_at' => Carbon::now()->toDateTimeString()]);

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
