<?php

namespace App\Http\Controllers;

use App\Jobs\sendProfileCreationEmail;
use App\Jobs\sendProfileCreationSMS;
use App\Library\Sms;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberController extends Controller
{
    use DispatchesJobs;

    public function search($member, Request $request)
    {
        $search = trim($request->search);

        $profile = Profile::with(['member' => function ($q) {
            $q->select('id', 'profile_id');
        }])->select('id', 'name', 'pro_pic')->where('email', $search)->first();

        if (count($profile) == 0) {
            $profile = Profile::with(['member' => function ($q) {
                $q->select('id', 'profile_id');
            }])->select('id', 'name', 'pro_pic')->where('mobile', $this->formatMobile($search))->first();
        }
        if (count($profile) != 0) {
            if ($profile->member != null) {
                if ($profile->member->id == $member) {
                    return response()->json(['msg' => "seriously??? can't send invitation to yourself", 'code' => 500]);
                }
            } else {
                array_forget($profile,'member');
                return response()->json(['profile' => $profile, 'code' => 200]);
            }
        } else {
            $this->dispatch(new sendProfileCreationEmail($search));
//            Sms::send_single_message($this->formatMobile($search), "Please go to this link to create your profile:" . env('SHEBA_ACCOUNT_URL'));
            return response()->json(['msg' => "we've send Member creation message", 'code' => 200]);
        }
    }

    public function getRequests($member)
    {
        $member = Member::with(['requests' => function ($q) {
            $q->select('id', 'member_id', 'business_id', 'status')->with(['business' => function ($q) {
                $q->select('id', 'name');
            }]);
        }])->select('id')->where('id', $member)->first();
        if (count($member->requests) > 0) {
            return response()->json(['code' => 200, 'requests' => $member->requests]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

    private function formatMobile($mobile)
    {
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $mobile)) {
            ;
            return $mobile;
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $mobile)) {
            return preg_replace('/^88/', '+88', $mobile);
        } // real mobile no add '+880' at the start
        else {
            return '+88' . $mobile;
        }
    }
}
