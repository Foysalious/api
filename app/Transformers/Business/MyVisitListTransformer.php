<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\BusinessMember;
use App\Models\Profile;
use App\Models\Member;

class MyVisitListTransformer extends TransformerAbstract
{
    public function transform($visit)
    {
        /** @var BusinessMember $visitor */
        $visitor = $visit->visitor;
        /** @var Member $member */
        $member = $visitor->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        $department = $visitor->department();

        return [
            'id' => $visit->id,
            'title' => $visit->title,
            'description' => $visit->description,
            'schedule_date' => $visit->schedule_date->format('d M, Y'),
            'status' => $visit->status,
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name ?: null,
                'pro_pic' => $profile->pro_pic,
                'department' => $department ? $department->name : null
            ]
        ];
    }
}