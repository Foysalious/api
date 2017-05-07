<?php

namespace App\Http\Controllers;

use App\Jobs\SendBusinessRequestEmail;
use App\Library\Sms;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Repositories\Business\BusinessRepository;
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
        if ($this->businessRepository->isValidURL($request->url) == false) {
            return response()->json(['code' => 409, 'msg' => 'url already taken!']);
        }
        return $this->businessRepository->create($member, $request) ? response()->json(['code' => 200, 'msg' => 'ok']) : response()->json(['code' => 500, 'msg' => 'try again!']);
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

    public function sendInvitationToMember($member, Request $request)
    {
        $member = Member::find($member);
        $business = $member->businesses()->where('businesses.id', $request->business_id)->first();
        if ($business != null) {
            $sendMember = Member::find($request->send_member_id);
            $sendRequest = new MemberRequest();
            $sendRequest->member_id = $request->send_member_id;
            $sendRequest->business_id = $business->id;
            $sendRequest->member_mobile = $sendMember->profile->mobile;
            $sendRequest->member_email = $sendMember->profile->email;
            $sendRequest->requester_type = $request->type;
            $sendRequest->save();
            if ($sendRequest->member_email != '') {
                $this->dispatch(new SendBusinessRequestEmail($sendRequest->member_email));
                $sendRequest->mail_sent = 1;
                $sendRequest->update();
            }
            if ($sendRequest->member_mobile != '') {
                Sms::send_single_message($sendRequest->member_mobile, "Please go to this link to see the invitation: " . env('SHEBA_ACCOUNT_URL'));
            }
            return response()->json(['code' => 200]);
        }
        return response()->json(['code' => 409]);
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

}
