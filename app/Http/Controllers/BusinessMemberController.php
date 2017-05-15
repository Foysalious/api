<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Repositories\BusinessMemberRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\MemberRepository;
use Illuminate\Http\Request;

class BusinessMemberController extends Controller
{
    private $businessRepository;
    private $businessMemberRepository;
    private $memberRepository;

    public function __construct()
    {
        $this->businessRepository = new BusinessRepository();
        $this->businessMemberRepository = new BusinessMemberRepository();
        $this->memberRepository = new MemberRepository();
    }

    public function getMember($member, $business, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessRepository->businessExistsForMember($member, $business);
        if ($business != null) {
            $member = $this->businessMemberRepository->isBusinessMember($business, $request->business_member);
            if ($member != null) {
                return response()->json(['member' => $this->memberRepository->getInfo($member), 'code' => 200]);
            } else {
                return response()->json(['code' => 409, 'msg' => 'conflict']);
            }
        } else {
            return response()->json(['code' => 409, 'msg' => 'conflict']);
        }
    }

    public function changeMemberType($member, $business, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessRepository->businessExistsForMember($member, $business);
        if ($business != null) {
            $member = $this->businessMemberRepository->isBusinessMember($business, $request->business_member_id);
            if ($member != null && $member->pivot->type == 'Admin') {
                if ($this->businessMemberRepository->changeType($business, $member, $request->type)) {
                    return response()->json(['code' => 200]);
                } else {
                    return response()->json(['code' => 500, 'msg' => 'try again']);
                }
            } else {
                return response()->json(['code' => 409, 'msg' => 'conflict']);
            }
        } else {
            return response()->json(['code' => 409, 'msg' => 'conflict']);
        }
    }
}
