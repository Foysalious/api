<?php

namespace App\Repositories;


class BusinessMemberRepository
{

    public function isBusinessMember($business, $id)
    {
        return $business->members()->where('members.id', $id)->first();
    }

    public function changeType($business, $member, $type)
    {
        try {
            DB::transaction(function () use ($business, $member, $type) {
                $business->members()->updateExistingPivot($member->id, ['type' => $type]);
            });
        } catch (QueryException $e) {
            return false;
        }
        return true;
    }

    public function isMemberAdmin($business, $member)
    {
        return $member->businesses()->where([
            ['businesses.id', $business],
            ['business_member.type', 'Admin']
        ])->first();
    }

}