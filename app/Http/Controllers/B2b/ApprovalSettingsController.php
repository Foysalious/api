<?php

namespace App\Http\Controllers\B2b;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalProcessApprover\ApprovalProcessApprover;

class ApprovalSettingsController extends Controller
{
    public function index(Request $request)
    {
       $approval_settings =  ApprovalSetting::where('business_id', $request->business->id)->get();
    }
}
