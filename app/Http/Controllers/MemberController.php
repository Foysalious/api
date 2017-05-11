<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Repositories\InvitationRepository;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private $invitationRepository;

    public function __construct()
    {
        $this->invitationRepository = new InvitationRepository();
    }

    public function getRequests($member)
    {
        $member = Member::with(['profile' => function ($q) {
            $q->select('id')->with(['joinRequests' => function ($q) {
                $q->select('id', 'profile_id', 'organization_type', 'organization_id')->where('status', 'Pending');
            }]);
        }])->select('id', 'profile_id')->where('id', $member)->first();
        foreach ($member->profile->joinRequests as $request) {
            array_add($request, 'business', $request->organization->select('id', 'name', 'sub_domain', 'logo', 'address')->first());
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
