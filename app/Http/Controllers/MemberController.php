<?php

namespace App\Http\Controllers;

use App\Jobs\sendProfileCreationEmail;
use App\Jobs\sendProfileCreationSMS;
use App\Library\Sms;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Profile;
use App\Repositories\InvitationRepository;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberController extends Controller
{
    use DispatchesJobs;
    private $invitationRepository;

    public function __construct()
    {
        $this->invitationRepository = new InvitationRepository();
    }

    public function search($member, Request $request)
    {
        $search = trim($request->search);
        $profile = $this->getProfile('email', $search);
        if (count($profile) == 0) {
            $profile = $this->getProfile('mobile', $this->formatMobile($search));
        }
        if (count($profile) != 0) {
            if ($profile->member != null) {
                if ($profile->member->id == $member) {
                    return response()->json(['msg' => "seriously??? can't send invitation to yourself", 'code' => 500]);
                }
            }
            array_forget($profile, 'member');
            return response()->json(['profile' => $profile, 'code' => 200]);
        } else {
            $this->dispatch(new sendProfileCreationEmail($search));
//            Sms::send_single_message($this->formatMobile($search), "Please go to this link to create your profile:" . env('SHEBA_ACCOUNT_URL'));
            return response()->json(['msg' => "we've send Member creation message", 'code' => 200]);
        }
    }

    public function getRequests($member)
    {
        $member = Member::with(['profile' => function ($q) {
            $q->select('id')->with(['joinRequests' => function ($q) {
                $q->select('id', 'profile_id', 'organization_type', 'organization_id');
            }]);
        }])->select('id', 'profile_id')->where('id', $member)->first();
        foreach ($member->profile->joinRequests as $request) {
            array_add($request, 'business', $request->organization->select('id', 'name', 'sub_domain', 'logo')->first());
            array_forget($request, 'organization');
            array_forget($request, 'organization_type');
            array_forget($request, 'organization_id');
        }
        if (count($member->profile->joinRequests) > 0) {
            return response()->json(['code' => 200, 'requests' => $member->profile->joinRequests]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

    private function formatMobile($mobile)
    {
        // mobile starts with ' + 88'
        if (preg_match("/^(\+88)/", $mobile)) {
            return $mobile;
        } // when mobile starts with '88' replace it with ' + 880'
        elseif (preg_match("/^(88)/", $mobile)) {
            return preg_replace(' /^88 / ', ' + 88', $mobile);
        } // real mobile no add ' + 880' at the start
        else {
            return ' + 88' . $mobile;
        }
    }

    private function getProfile($field, $search)
    {
        return Profile::with(['member' => function ($q) {
            $q->select('id', 'profile_id');
        }])->select('id', 'name', 'pro_pic')->where($field, $search)->first();

    }

    public function manageInvitation($member, Request $request)
    {
        $join_request = Member::find($member)->profile->joinRequests()->where('id', $request->invitation)->first();
        if (count($join_request) != 0) {
            return $this->invitationRepository->manage($join_request, $request->status) ? response()->json(['code' => 200]) : response()->json(['code' => 500]);
        } else {
            return response()->json(['code' => 409]);
        }
    }
}
