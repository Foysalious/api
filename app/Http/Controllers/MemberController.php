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
        $query = Profile::with(['member' => function ($q) {
            $q->select('id', 'profile_id');
        }])->select('id', 'name', 'pro_pic');

        if ($request->has('email')) {
            $profile = $query->where('email', $request->email)->first();
        } elseif ($request->has('mobile')) {
            $profile = $query->where('mobile', '+88' . $request->mobile)->first();
        }
        if ($profile != null && $profile->member != null) {
            if ($profile->member->id == $member) {
                return response()->json(['msg' => "seriously??? can't send invitation to yourself", 'code' => 500]);
            }
            return response()->json(['result' => $profile, 'msg' => 'found', 'code' => 200]);
        } else {
            if ($request->has('email')) {
                $this->dispatch(new sendProfileCreationEmail($request->email));
            } elseif ($request->has('mobile')) {
                Sms::send_single_message('+88' . $request->mobile, "Please go to this link to create your profile:" . env('SHEBA_ACCOUNT_URL'));
            }
            return response()->json(['msg' => "we've send profile creation message", 'code' => 200]);
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
        }
        else{
            return response()->json(['code' => 404]);
        }
    }
}
