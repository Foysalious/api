<?php

namespace App\Repositories;


class BusinessMemberRepository
{

    public function isBusinessMember($business, $id)
    {
        return $business->members()->where('members.id', $id)->first();
    }

}