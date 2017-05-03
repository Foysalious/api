<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Member;
use App\Repositories\Business\BusinessRepository;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    private $businessRepository;

    public function __construct()
    {
        $this->businessRepository = new BusinessRepository();
    }

    public function checkURL(Request $request)
    {
        return $this->businessRepository->isValidURL($request->url) ? response()->json(['code' => 200, 'msg' => 'good to go']) : response()->json(['code' => 409, 'msg' => 'already exists']);
    }

    public function show($member)
    {
        $businesses = $this->businessRepository->getBusinesses($member);
        return count($businesses) > 0 ? response()->json(['code' => 200, 'businesses' => $businesses]) : response()->json(['code' => 409, 'msg' => 'nothing found!']);
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
        $member = Member::find($member);
        $business = $member->businesses()->where('businesses.id', $business)->first();
        $business->logo = $this->businessRepository->uploadLogo($business, $request->file('logo'));
        $business->logo_original = $business->logo;
        if ($business->update()) {
            return response()->json(['code' => '200']);
        }
    }

}
