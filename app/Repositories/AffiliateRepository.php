<?php

namespace App\Repositories;


use App\Models\Partner;

class AffiliateRepository
{
    public function sortAgents($request, $agents)
    {
        $sortBy = 'name';
        if ($request->has('sort')) {
            $sortBy = $request->sort;
            if ($sortBy != 'name') {
                array_multisort(array_column($agents, $sortBy), SORT_DESC, $agents);
            } else {
                array_multisort(array_column($agents, $sortBy), SORT_STRING, $agents);
            }
        } else {
            array_multisort(array_column($agents, $sortBy), SORT_STRING, $agents);
        }
        return $agents;
    }

    public function moderatedPartner($request, $status=null)
    {
        list($offset, $limit) = calculatePagination($request);
        $query = $request->get('query');
        return $request->affiliate->load(['onboardedPartners' => function ($q) use ($offset, $limit, $status, $query) {
            $q->offset($offset)->limit($limit)
//                    ->whereHas('resources.profile', function ($que) use ($query) {
//                        if ($query) {
//                            $que->where('profiles.name', 'LIKE', '%' . $query . '%');
//                        }
//                    })
                ->with('resources.profile')->orderBy('created_at', 'desc')->where('package_id', 1);
            $status == 'pending' ? $q->where('moderation_status', $status) : $q->where(function ($qu) {
                return $qu->where('moderation_status', 'rejected')->orWhere('moderation_status', 'approved');
            });
            if ($query) $q->where('name', 'LIKE', '%' . $query . '%');
        }]);
    }

    public function mapForModerationApi(Partner $partner)
    {
        $resource = $partner->getFirstAdminResource();
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'resource' => !$resource ? null : [
                'id' => $resource->id,
                'name' => $resource->profile->name,
                'mobile' => $resource->profile->mobile,
            ],
            'moderation_status' => $partner->moderation_status,
            'income' => $partner->moderation_status == 'approved' ? constants('AFFILIATION_LITE_ONBOARD_REWARD') : 0,
            'created_at' => $partner->created_at->toDateTimeString()
        ];
    }
}
