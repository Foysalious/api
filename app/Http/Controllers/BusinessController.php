<?php

namespace App\Http\Controllers;

use App\Jobs\SendBusinessRequestEmail;
use App\Jobs\SendProfileCreationEmail;
use App\Library\Sms;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\JoinRequest;
use App\Models\Member;
use App\Models\Profile;
use App\Repositories\BusinessRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    use DispatchesJobs;
    private $businessRepository;

    public function __construct()
    {
        $this->businessRepository = new BusinessRepository();
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
        $this->validate($request, [
            'email' => 'unique:businesses',
            'phone' => 'unique:businesses',
        ]);
        $business = $this->businessRepository->create($member, $request);
        return $business != false ? response()->json(['code' => 200, 'business' => $business->id, 'msg' => 'ok']) : response()->json(['code' => 500, 'msg' => 'try again!']);
    }

    public function update($member, $business, Request $request)
    {
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
            $q->select('businesses.id', 'name', 'logo', 'sub_domain', 'business_category_id', 'email', 'phone', 'businesses.type', 'address', 'employee_size')->where('business_member.business_id', $business);
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

    public function sendInvitationToMember($member, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessExistsForMember($member, $request->business);
        if ($business != null) {
            if ($this->businessRepository->sendInvitation($request)) {
                return response()->json(['msg' => 'ok', 'code' => 200]);
            } else {
                return response()->json(['msg' => 'not ok!', 'code' => 500]);
            }
        }
        return response()->json(['code' => 409, 'msg' => "this business doesn't belong to you"]);
    }

    private function businessExistsForMember($member, $id)
    {
        return $member->businesses()->where('businesses.id', $id)->first();
    }

    public function checkBusiness($member, Request $request)
    {
        $member = Member::find($member);
        $business = $this->businessExistsForMember($member, $request->business);
        if ($business != null) {
            return response(['code' => 200]);
        } else {
            return response(['code' => 404]);
        }
    }

}
