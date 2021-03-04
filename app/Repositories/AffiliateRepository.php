<?php namespace App\Repositories;

use App\Models\Affiliate;
use App\Models\HyperLocal;
use App\Models\Partner;
use App\Sheba\LightOnBoarding\PartnerModerator;
use Sheba\ModificationFields;

class AffiliateRepository
{
    use ModificationFields;
    /**
     * @param $request
     * @param $agents
     * @return mixed
     */
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

    /**
     * @param $request
     * @param null $status
     * @return mixed
     */
    public function moderatedPartner($request, $status = null)
    {
        list($offset, $limit) = calculatePagination($request);
        $query = $request->get('query');
        return $request->affiliate->load(['moderatedPartners' => function ($q) use ($offset, $limit, $status, $query) {
            $q->with('resources.profile')->orderBy('created_at', 'desc')->where('package_id', 1);
            if ($status == 'pending') {
                $q->where(function ($qu) {
                    $qu->where('moderation_status', 'pending')->orWhereNull('moderation_status');
                });
            } else {
                $q->where(function ($qu) {
                    return $qu->where('moderation_status', 'rejected')->orWhere('moderation_status', 'approved');
                })->offset($offset)->limit($limit);
            }
            if ($query) {
                $q->where(function ($qu) use ($query) {
                    return $qu->where('name', 'LIKE', '%' . $query . '%')->orWhereHas('resources.profile', function ($qq) use ($query) {
                        $qq->where('mobile', 'LIKE', '%' . $query . '%');
                    });
                });
            }
        }]);
    }

    /**
     * @param Partner $partner
     * @param null $source
     * @param bool $isDetails
     * @return array
     */
    public function mapForModerationApi(Partner $partner, $source = null, $isDetails = false)
    {
        $resource = $partner->getFirstAdminResource();
        $geo_info = json_decode($partner->geo_informations, true);
        $location = $geo_info ? HyperLocal::insidePolygon($geo_info['lat'], $geo_info['lng'])->first() : null;
        $details = [
            'id' => $partner->id,
            'name' => $partner->name,
            'resource' => !$resource ? null : [
                'id' => $resource->id,
                'name' => $resource->profile->name,
                'mobile' => $resource->profile->mobile,
            ],
            'address' => $partner->address,
            'logo' => $resource->profile->pro_pic,
            'location' => $location ? $location->location->name : null,
            'location_id' => $location ? $location->location_id : null,
            'moderation_status' => $partner->moderation_status,
            'income' => $partner->moderation_status == 'approved' ? constants('AFFILIATION_LITE_ONBOARD_REWARD') : 0,
            'created_at' => $partner->created_at->toDateTimeString()
        ];
        if (!$isDetails) {
            $details['distance'] = $source && $geo_info ? PartnerModerator::calculateDistance($source, (array)$geo_info) : 9999999999;
        } else {
            $details['geo_informations'] = $partner->geo_informations ? json_decode($partner->geo_informations) : null;
            $details ['services'] = $partner->services()->select('services.id', 'services.name')->get()->map(function ($service) {
                return ['name' => $service->name, 'id' => $service->id];
            });
        }
        return $details;
    }

    public function createAffiliate($resource)
    {
        $affiliate                      = new Affiliate();
        $affiliate->profile_id          = $resource->profile_id;
        $affiliate->remember_token      = str_random(255);
        $affiliate->verification_status = $resource->status == "unverified" ? "pending" : $resource->status;
        $this->withCreateModificationField($affiliate);
        $affiliate->save();
    }
}
