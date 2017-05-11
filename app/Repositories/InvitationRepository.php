<?php

namespace App\Repositories;

use App\Jobs\SendBusinessRequestEmail;
use App\Models\JoinRequest;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Foundation\Bus\DispatchesJobs;

class InvitationRepository
{
    use DispatchesJobs;

    public function manage($join_request, $status)
    {
        if (in_array($join_request->status, ['Accepted', 'Rejected'])) {
            return false;
        }
        try {
            if ($status == 'accept') {
                $join_request->status = 'Accepted';
                $join_request->requestor()->members()->attach(Profile::find($join_request->profile_id)->member->id);
            } elseif ($status == 'reject') {
                $join_request->status = 'Rejected';
            }
            $join_request->update();
        } catch (QueryException $e) {
            return false;
        }
        return true;
    }

    public function send($member, $request)
    {
        if ($request->sender == 'business') {
            $joinRequest = new JoinRequest();
            if ($request->profile != '') {
                $profile = Profile::find($request->profile);
                $joinRequest->profile_id = $profile->id;
                $joinRequest->profile_email = $profile->email;
                $joinRequest->profile_mobile = $profile->mobile;
            } elseif ($request->search != null && filter_var($request->search, FILTER_VALIDATE_EMAIL)) {
                $joinRequest->profile_email = $request->search;
            } else {
                return false;
            }
            $joinRequest->organization_id = $request->business;
            $joinRequest->organization_type = $joinRequest->requester_type = "App\Models\Business";
            $joinRequest->save();
            if ($joinRequest->profile_email != '') {
                $this->dispatch(new SendBusinessRequestEmail($joinRequest->profile_email));
                $joinRequest->mail_sent = 1;
                $joinRequest->update();
            }
            return true;
        } elseif ($request->sender = 'member') {
            $joinRequest = new JoinRequest();
            $profile = Member::find($member)->profile;
            $joinRequest->profile_id = $profile->id;
            $joinRequest->profile_email = $profile->email;
            $joinRequest->profile_mobile = $profile->mobile;
        }
        $joinRequest->organization_id = $request->business;
        $joinRequest->organization_type = "App\Models\Business";
        $joinRequest->requester_type = "App\Models\Profile";
        $joinRequest->save();
        return true;
    }


}