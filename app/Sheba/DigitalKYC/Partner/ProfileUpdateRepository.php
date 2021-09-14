<?php namespace App\Sheba\DigitalKYC\Partner;

use App\Sheba\Partner\KYC\Statuses;
use Illuminate\Http\Request;
use Sheba\Partner\KYC\RestrictedFeature;
use Sheba\Repositories\ProfileRepository;

class ProfileUpdateRepository
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function createData(Request $request)
    {
        $profile = $request->manager_resource->profile;

        if ($request->type != 'image') {
            $data['name'] = $request->name;
            $data['nid_no'] = $profile->nid_verified == 1 ? $profile->nid_no : $request->nid_no;
            $data['dob'] = $request->dob;
        }
        if ($request->type != 'info') {
            $data['nid_image_front'] = $request->nid_image_front;
            $data['nid_image_back'] = $request->nid_image_back;
            $data['profile_image'] = (new ProfileRepository($profile))->saveProPic($request->pro_pic, $profile->name);
        }

        return $data;
    }

    public function createDataForNidOcr($nid_image_front, $nid_image_back, $nid_no): array
    {
        $data['nid_image_front'] = $nid_image_front;
        $data['nid_image_back']  = $nid_image_back;
        $data['nid_no']          = $nid_no;
        return $data;
    }

//    public function createDataForPorichoyEkyc($person_photo)
//    {
//        $data['pro_pic'] = $person_photo;
//        return $data;
//    }

    /**
     * @param Request $request
     * @return array
     */
    public function checkNid(Request $request)
    {
        $resource = $request->manager_resource;
        $profile = $resource->profile;
        $status = $resource->status;

        if ($status == Statuses::VERIFIED) {

             return $data = [
                'message' => [
                    'en' => 'Profile verified',
                    'bn' => 'Profile ভেরিফাইড'
                ],
                'status' => Statuses::VERIFIED,
                'message_seen' => $resource->verification_message_seen,
                'nid_verification_request_count' =>  $profile->nid_verification_request_count
            ];
        }

        if ($status == Statuses::UNVERIFIED) {
            return $data = [
                'message' => [
                    'en' => 'NID has not been submitted',
                    'bn' => 'আপনার NID দেয়া হয় নি। সকল ফিচার ব্যাবহার করতে NID ভেরিফিকেশন করুন'
                ],
                'status' => Statuses::UNVERIFIED,
                'restricted_feature' => $this->getRestrictedFeature(),
                'nid_verification_request_count' =>  $profile->nid_verification_request_count
            ];
        } else {
            $data = [
                'message' => [
                    'en' => $status == Statuses::PENDING ? 'Profile verification process pending' : 'Profile verification rejected',
                    'bn' => $status == Statuses::PENDING ? 'আপনার NID ভেরিফিকেশন প্রক্রিয়াধীন রয়েছে। ভেরিফিকেশন প্রক্রিয়া দ্রুত করতে চাইলে ১৬১৬৫ নাম্বারে যোগাযোগ করুন' : 'দুঃখিত। আপনার NID ভেরিফিকেশন সফল হয় নি।পুনরায় চেষ্টা করুন।'
                ],
                'status' => $resource->status,
                'restricted_feature' => $this->getRestrictedFeature(),
                'nid_verification_request_count' =>  $profile->nid_verification_request_count,
            ];
            if ($status == Statuses::REJECTED)
            {
                list($count, $reason) = $this->getRejectCountAndReason($request->manager_resource);
                $data['reject_count'] = $count;
                $data['reason'] = $reason;
            }

            return $data;
        }
    }


    /**
     * @return string[]
     */
    private function getRestrictedFeature()
    {
        return RestrictedFeature::get();
    }

    /**
     * @param $resource
     * @return array
     */
    private function getRejectCountAndReason($resource)
    {
        $rejected_logs = $resource->statusChangeLog->where('to', Statuses::REJECTED);
        return ([!$rejected_logs->isEmpty() ? $rejected_logs->count() : 1, !$rejected_logs->isEmpty() ? $rejected_logs->last()->reason : null]);
    }

    public function updateSeenStatus($resource,$seen_status)
    {
        $resource->verification_message_seen = $seen_status;
        $resource->update();
    }

}
