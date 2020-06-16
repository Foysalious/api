<?php namespace App\Sheba\DigitalKYC\Partner;

use App\Sheba\Partner\KYC\RestrictedFeature;
use Illuminate\Http\Request;
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
            $data['nid_no'] = $request->nid_no;
            $data['dob'] = $request->dob;
        }
        if ($request->type != 'info') {
            $data['nid_image_front'] = $request->nid_image_front;
            $data['nid_image_back'] = $request->nid_image_back;
            $data['profile_image'] = (new ProfileRepository($profile))->saveProPic($request->pro_pic, $profile->name);
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function checkNid(Request $request)
    {
        $resource = $request->manager_resource;
        $profile = $resource->profile;
        $status = $resource->status;

        if ($status == 'verified') {
            $data = [
                'message' => [
                    'en' => 'NID verified',
                    'bn' => 'NID ভেরিফাইড'
                ],
                'status' => 'verified',
            ];
        }

        if ($status == 'unverified') {
            $data = [
                'message' => [
                    'en' => 'NID has not been submitted',
                    'bn' => 'আপনার NID দেয়া হয় নি'
                ],
                'status' => 'not_submitted',
                'restricted_feature' => $this->getRestrictedFeature(),
            ];
        } else {
            $data = [
                'message' => [
                    'en' => $status == 'pending' ? 'NID verification process pending' : 'NID Rejected',
                    'bn' => $status == 'pending' ? 'আপনার ভেরিফিকেশন প্রক্রিয়াধীন রয়েছে। দ্রুত করতে চাইলে ১৬১৬৫ নাম্বারে যোগাযোগ করুন' : 'দুঃখিত। আপনার ভেরিফিকেশন সফল হয় নি।'
                ],
                'status' => $resource->status,
                'restricted_feature' => $this->getRestrictedFeature(),
            ];
            if ($status == 'rejected')
            {
                list($count, $reason) = $this->getRejectCountAndReason($request->manager_resource);
                $data['reject_count'] = $count;
                $data['reason'] = $reason;
            }

        }
        $data['nid_verification_rejection_count'] = $profile->nid_verification_request_count;

        return $data;
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
        $rejected_logs = $resource->statusChangeLog->where('to', 'rejected');
        return ([!$rejected_logs->isEmpty() ? $rejected_logs->count() : 1, !$rejected_logs->isEmpty() ? $rejected_logs->last()->reason : null]);
    }

    public function updateSeenStatus($resource,$seen_status)
    {
        $resource->verification_message_seen = $seen_status;
        $resource->update();
    }

}