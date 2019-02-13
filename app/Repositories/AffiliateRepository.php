<?php

namespace App\Repositories;


use App\Models\HyperLocal;
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

    public function moderatedPartner($request, $status = null)
    {
        list($offset, $limit) = calculatePagination($request);
        $query = $request->get('query');
        return $request->affiliate->load(['onboardedPartners' => function ($q) use ($offset, $limit, $status, $query) {
            $q->offset($offset)->limit($limit)
                ->with('resources.profile')->orderBy('created_at', 'desc')->where('package_id', 1);
            $status == 'pending' ? $q->where('moderation_status', $status) : $q->where(function ($qu) {
                return $qu->where('moderation_status', 'rejected')->orWhere('moderation_status', 'approved');
            });
            if ($query) {
                $q->where(function ($qu) use ($query) {
                    return $qu->where('name', 'LIKE', '%' . $query . '%')->orWhere('mobile', 'LIKE', '%' . $query . '%');
                });
            }
        }]);
    }

    public function mapForModerationApi(Partner $partner)
    {
        $resource = $partner->getFirstAdminResource();
        $geo_info = json_decode($partner->geo_informations);
        $location = $geo_info ? $partner->locations->first() : null;
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'resource' => !$resource ? null : [
                'id' => $resource->id,
                'name' => $resource->profile->name,
                'mobile' => $resource->profile->mobile,
            ],
            'location' => $location ? $location->name : null,
            'location_id' => $location ? $location->id : null,
            'moderation_status' => $partner->moderation_status,
            'income' => $partner->moderation_status == 'approved' ? constants('AFFILIATION_LITE_ONBOARD_REWARD') : 0,
            'created_at' => $partner->created_at->toDateTimeString()
        ];
    }
}
