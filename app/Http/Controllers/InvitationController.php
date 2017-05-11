<?php


namespace App\Http\Controllers;


use App\Models\Member;
use App\Repositories\BusinessRepository;
use App\Repositories\InvitationRepository;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    private $invitationRepository;
    private $businessRepository;

    public function __construct()
    {
        $this->invitationRepository = new InvitationRepository();
        $this->businessRepository = new BusinessRepository();
    }

    public function sendInvitation($member, Request $request)
    {
        if ($request->sender == 'business') {
            $member = Member::find($member);
            $business = $this->businessRepository->businessExistsForMember($member, $request->business);
            if ($business != null) {
                if ($this->invitationRepository->send($member,$request)) {
                    return response()->json(['msg' => 'ok', 'code' => 200]);
                } else {
                    return response()->json(['msg' => "couldn't send invitation to member!", 'code' => 500]);
                }
            }
            return response()->json(['code' => 409, 'msg' => "this business doesn't belong to you"]);
        } elseif ($request->sender == 'member') {
            if ($this->invitationRepository->send($member,$request)) {
                return response()->json(['msg' => 'ok', 'code' => 200]);
            } else {
                return response()->json(['msg' => "couldn't send invitation to business!", 'code' => 500]);
            }
        }

    }

}