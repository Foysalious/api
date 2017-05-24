<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Member;
use App\Repositories\BusinessMemberRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\InvitationRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    use DispatchesJobs;
    private $businessRepository;
    private $businessMemberRepository;
    private $invitationRepository;

    public function __construct()
    {
        $this->businessRepository = new BusinessRepository();
        $this->businessMemberRepository = new BusinessMemberRepository();
        $this->invitationRepository = new InvitationRepository();
    }


    public function checkURL(Request $request)
    {
        return $this->businessRepository->isValidURL($request->url, $request->business) ? response()->json(['code' => 200, 'msg' => 'good to go']) : response()->json(['code' => 409, 'msg' => 'already exists']);
    }

    public function show($member)
    {
        $members = $this->businessRepository->getBusinesses($member);
        return count($members->businesses) > 0 ? response()->json(['code' => 200, 'businesses' => $members->businesses]) : response()->json(['code' => 404, 'msg' => 'nothing found!']);
    }

    public function create($member, Request $request)
    {
        $msg = '';
        $exists = false;
        if ($this->businessRepository->ifExist('email', $request->email)) {
            $msg = 'email';
            $exists = true;
        }
        if ($this->businessRepository->ifExist('phone', $request->phone)) {
            $msg = $msg . ' & phone';
            $exists = true;
        }
        if ($exists) {
            return response()->json(['code' => 409, 'msg' => $msg . ' already taken!']);
        }
        $business = $this->businessRepository->create($member, $request);
        return $business != false ? response()->json(['code' => 200, 'business' => $business->id, 'msg' => 'ok']) : response()->json(['code' => 500, 'msg' => 'try again!']);
    }

    public function update($member, $business, Request $request)
    {
        $msg = '';
        $exists = false;
        if ($this->businessRepository->ifExist('email', $request->email,$business)) {
            $msg = 'email';
            $exists = true;
        }
        if ($this->businessRepository->ifExist('phone', $request->phone,$business)) {
            $msg = $msg . ' & phone';
            $exists = true;
        }
        if ($exists) {
            return response()->json(['code' => 409, 'msg' => $msg . ' already taken!']);
        }
        if ($this->businessRepository->isValidURL($request->url, $business) == false) {
            return response()->json(['code' => 409, 'msg' => 'url already taken!']);
        }
        $business = Business::find($business);
        return $this->businessRepository->update($business, $request) ? response()->json(['code' => 200, 'msg' => 'ok']) : response()->json(['code' => 500, 'msg' => 'try again!']);
    }

    public function changeLogo($member, $business, Request $request)
    {
        $business = Business::find($business);
        $business->logo = $this->businessRepository->uploadLogo($business, $request->file('logo'));
        $business->logo_original = $business->logo;
        if ($business->update()) {
            return response()->json(['code' => 200]);
        }
    }

    public function getBusiness($member, $business)
    {
        $member = Member::with(['businesses' => function ($q) use ($business) {
            $q->select('businesses.id', 'name', 'logo', 'sub_domain', 'business_category_id', 'email', 'phone', 'description',
                'businesses.type', 'address', 'employee_size')->where('business_member.business_id', $business)->with(['businessCategory' => function ($q) {
                $q->select('id', 'name');
            }]);
        }])->select('id')->where('id', $member)->first();
        if (count($member) != 0) {
            array_forget($member->businesses[0], 'pivot');
            return response()->json(['code' => 200, 'business' => $member->businesses[0]]);
        }
    }

    public function getTypeAndCategories()
    {
        $types = constants('BUSINESS_TYPES');
        $categories = BusinessCategory::select('id', 'name')->where('publication_status', 1)->get();
        return response()->json(['types' => $types, 'categories' => $categories]);
    }

    public function checkBusiness($member, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessRepository->businessExistsForMember($member, $request->business);
        return $business != null ? response(['code' => 200]) : response(['code' => 404]);
    }

    public function getMembers($member, $business, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessRepository->businessExistsForMember($member, $business);
        if ($business != null) {
            $business = Business::with(['members' => function ($q) {
                $q->select('members.id', 'members.profile_id')->with(['profile' => function ($q) {
                    $q->select('profiles.id', 'profiles.name');
                }]);
            }])->where('id', $business->id)->select('id')->first();
            foreach ($business->members as $member) {
                $member['join_date'] = $member->pivot->join_date;
                $member['type'] = $member->pivot->type;
                array_forget($member, 'pivot');
                array_forget($member, 'profile_id');
            }
            return response(['code' => 200, 'members' => $business->members]);
        } else {
            return response(['code' => 404]);
        }
    }

    public function getRequests($member, $business, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessRepository->businessExistsForMember($member, $business);
        if ($business != null) {
            $business = Business::with(['joinRequests' => function ($q) {
                $q->select('id', 'profile_id', 'profile_email', 'organization_id', 'requester_type')->where('status', 'Pending')->with(['profile' => function ($q) {
                    $q->select('id', 'name', 'email', 'mobile');
                }]);
            }])->where('id', $business->id)->first();
            if (count($business->joinRequests) > 0) {
                return response()->json(['code' => 200, 'requests' => $business->joinRequests]);
            } else {
                return response()->json(['code' => 404]);
            }
        }
    }

    public function manageInvitation($member, $business, Request $request)
    {
        $member = Member::find($member);
        if ($this->businessMemberRepository->isMemberAdmin($business, $member) != null) {
            $business = Business::find($business);
            $join_request = $business->joinRequests()->where('id', $request->invitation)->first();
            if (count($join_request) != 0) {
                return $this->invitationRepository->manage($join_request, $request->status) ? response()->json(['code' => 200]) : response()->json(['code' => 500]);
            } else {
                return response()->json(['code' => 409]);
            }
        }
        return response()->json(['msg' => 'You have no authorization', 'code' => 404]);

    }
}
