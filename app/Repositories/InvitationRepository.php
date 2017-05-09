<?php

namespace App\Repositories;

use App\Models\Profile;

class InvitationRepository
{
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
}